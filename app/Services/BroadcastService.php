<?php

namespace App\Services;

use App\Models\BroadcastCampaign;
use App\Models\BroadcastRecipient;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

class BroadcastService
{
    protected GowaService $gowa;

    public function __construct(GowaService $gowa)
    {
        $this->gowa = $gowa;
    }

    /**
     * Memproses pengiriman pesan untuk satu broadcast campaign
     */
    public function processCampaign(BroadcastCampaign $campaign): void
    {
        // Hindari proses jika campaign sudah selesai atau dibatalkan
        if (in_array($campaign->status, ['completed', 'cancelled'])) {
            return;
        }

        $device = $campaign->device;
        if (!$device || $device->status !== 'connected') {
            Log::warning("Broadcast #{$campaign->id} ditunda: Device {$campaign->device_id} belum terhubung.");
            $campaign->update(['status' => 'paused']);
            return;
        }

        // Tandai status campaign sedang berjalan
        $campaign->update([
            'status' => 'processing',
            'started_at' => $campaign->started_at ?: now(),
        ]);

        $pendingRecipients = $campaign->recipients()
            ->where('status', 'pending')
            ->orderBy('id', 'asc')
            ->get();

        $totalPending = $pendingRecipients->count();
        $processedThisRun = 0;

        foreach ($pendingRecipients as $recipient) {
            // Cek apakah ada perubahan status campaign saat proses (misal user menekan Jeda / Batal)
            $liveStatus = BroadcastCampaign::where('id', $campaign->id)->value('status');
            if (in_array($liveStatus, ['paused', 'cancelled'])) {
                Log::info("Broadcast #{$campaign->id} dihentikan sementara: status berubah menjadi {$liveStatus}.");
                return;
            }

            // Tandai recipient sedang diproses
            $recipient->update(['status' => 'processing']);

            try {
                $response = $this->gowa->sendMessage(
                    $device->gowa_session_id,
                    $recipient->number,
                    $recipient->message
                );

                if ($response && $response->successful()) {
                    $recipient->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'error_message' => null,
                        'response_data' => substr($response->body(), 0, 500),
                    ]);

                    // Catat ke tabel messages agar muncul di riwayat Live Chat
                    Message::create([
                        'device_id' => $device->id,
                        'user_id' => $campaign->user_id,
                        'number' => $recipient->number,
                        'message' => $recipient->message,
                        'direction' => 'out',
                        'status' => 'sent',
                    ]);

                    $campaign->increment('sent_count');
                } else {
                    $errorMsg = 'Gagal mengirim pesan via WhatsApp Gateway.';
                    if ($response) {
                        $json = $response->json();
                        $errorMsg = $json['message'] ?? $json['error'] ?? $response->body();
                    }

                    $recipient->update([
                        'status' => 'failed',
                        'sent_at' => now(),
                        'error_message' => $errorMsg,
                        'response_data' => $response ? substr($response->body(), 0, 500) : null,
                    ]);

                    $campaign->increment('failed_count');
                }
            } catch (\Exception $e) {
                $recipient->update([
                    'status' => 'failed',
                    'sent_at' => now(),
                    'error_message' => 'Exception: ' . $e->getMessage(),
                ]);

                $campaign->increment('failed_count');
            }

            $processedThisRun++;

            // ANTI-BANNED DELAY: Berikan jeda acak di antara setiap pengiriman pesan
            if ($processedThisRun < $totalPending) {
                $min = max(1, (int) $campaign->delay_min);
                $max = max($min, (int) $campaign->delay_max);
                $sleepSeconds = rand($min, $max);

                sleep($sleepSeconds);
            }
        }

        // Cek sisa antrean recipient
        $remaining = $campaign->recipients()->where('status', 'pending')->count();
        if ($remaining === 0) {
            $campaign->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }
    }

    /**
     * Memproses seluruh antrean broadcast terjadwal yang sudah memasuki waktu kirim
     */
    public function processScheduledCampaigns(): int
    {
        $now = now();
        $campaigns = BroadcastCampaign::where(function ($query) use ($now) {
                $query->where('status', 'scheduled')
                      ->where('scheduled_at', '<=', $now);
            })
            ->orWhere('status', 'processing')
            ->get();

        $count = 0;
        foreach ($campaigns as $campaign) {
            $this->processCampaign($campaign);
            $count++;
        }

        return $count;
    }

    /**
     * Menjalankan proses broadcast secara asinkron di latar belakang (Non-Blocking)
     */
    public function dispatchBackground(int $campaignId): void
    {
        $phpBinary = PHP_BINARY;
        $artisan = base_path('artisan');

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows Command: start /B menjalankan di background tanpa pop-up window
            $cmd = "start /B \"\" \"{$phpBinary}\" \"{$artisan}\" broadcast:process {$campaignId} > NUL 2>&1";
            pclose(popen($cmd, 'r'));
        } else {
            // Linux / Unix
            $cmd = "\"{$phpBinary}\" \"{$artisan}\" broadcast:process {$campaignId} > /dev/null 2>&1 &";
            exec($cmd);
        }
    }
}
