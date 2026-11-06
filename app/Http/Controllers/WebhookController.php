<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WebhookLog;
use App\Models\Device;
use App\Models\Message;
use App\Models\Ticket;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Services\GowaService; // Pastikan namespace GowaService Anda sudah benar

class WebhookController extends Controller
{
    // Masukkan GowaService ke dalam parameter handle agar bisa mengirim pesan
    public function handle(Request $request, GowaService $gowa)
    {
        $data = $request->all();
        \Log::info('--- MENGINTIP ISI WEBHOOK GOWA v9 ---');
        \Log::info(json_encode($data, JSON_PRETTY_PRINT));

        // 0. Verifikasi HMAC Signature (jika webhook secret dikonfigurasi di SIMAWA)
        $webhookSecret = config('services.gowa.webhook_secret');
        $hubSignature = $request->header('X-Hub-Signature-256');
        if ($webhookSecret && $hubSignature) {
            $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $webhookSecret);
            if (!hash_equals($expected, $hubSignature)) {
                \Log::warning('Webhook GOWA: Invalid HMAC signature!');
                return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
            }
        }

        $event = $request->input('event');
        $rawDeviceId = $request->input('device_id');
        $sessionId = $request->input('session_id');
        $deviceNumber = $rawDeviceId ? explode('@', explode(':', $rawDeviceId)[0])[0] : null;

        // Cari Device di SIMAWA:
        // Prioritas 1: Berdasarkan session_id GOWA (paling presisi untuk multi-device di GOWA v9)
        $device = null;
        if (!empty($sessionId)) {
            $device = Device::where('gowa_session_id', $sessionId)->first();
        }

        // Prioritas 2: Fallback ke nomor HP atau gowa_session_id
        if (!$device && !empty($deviceNumber)) {
            $device = Device::where('phone_number', $deviceNumber)
                ->orWhere('gowa_session_id', $deviceNumber)
                ->first();
        }

        // Event: device.logged_out (Fitur GOWA v9 ketika device terputus dari WhatsApp)
        if ($event === 'device.logged_out') {
            if ($device && $device->status !== 'disconnected') {
                $device->update(['status' => 'disconnected']);
                \Log::warning("Webhook GOWA: Device [{$device->name}] diputus dari WhatsApp: " . data_get($data, 'payload.reason', 'Logged out'));
            }
            return response()->json(['status' => 'success', 'message' => 'Device logged out handled']);
        }

        // 1. Abaikan jika ini bukan event pesan masuk
        if ($event !== 'message') {
            return response()->json(['status' => 'ignored', 'reason' => 'Bukan event message']);
        }

        if (!$device) {
            \Log::error("Webhook GOWA: Device dengan session '{$sessionId}' / nomor '{$deviceNumber}' tidak ditemukan di database SIMAWA.");
            return response()->json(['status' => 'error', 'message' => 'Device not found'], 404);
        }

        // Auto-update nomor WhatsApp device jika di database masih kosong
        if (empty($device->phone_number) && !empty($deviceNumber)) {
            $device->update(['phone_number' => $deviceNumber]);
        }

        $payload = $request->input('payload');

        // 2. Ekstrak Nomor Pengirim, Isi Pesan, dan Media
        $rawFrom = data_get($payload, 'from');
        $senderNumber = $rawFrom ? explode('@', explode(':', $rawFrom)[0])[0] : 'unknown';
        // GOWA v9 menyediakan sender_display_name yang lebih rapi
        $fromName = data_get($payload, 'sender_display_name') ?: data_get($payload, 'from_name');
        $text = data_get($payload, 'body') ?? '';
        $isFromMe = data_get($payload, 'is_from_me', false);
        $chatId = data_get($payload, 'chat_id') ?? '';

        // Tangkap kutipan pesan (quote reply)
        $quotedBody = data_get($payload, 'quoted_body');
        $repliedToId = data_get($payload, 'replied_to_id');

        if (empty($quotedBody) && !empty($repliedToId)) {
            $quotedBody = '📷 [Membalas Lampiran / File Media]';
        }

        // Ekstrak Media: Image, Document, Video, Audio, Sticker (GOWA v9)
        $mediaPath = null;
        $mediaItems = [
            'image'    => data_get($payload, 'image'),
            'document' => data_get($payload, 'document'),
            'video'    => data_get($payload, 'video'),
            'audio'    => data_get($payload, 'audio'),
            'sticker'  => data_get($payload, 'sticker'),
        ];

        foreach ($mediaItems as $mediaData) {
            if (empty($mediaData)) continue;
            if (is_array($mediaData)) {
                $mediaPath = $mediaData['path'] ?? $mediaData['url'] ?? null;
            } elseif (is_string($mediaData)) {
                $mediaPath = $mediaData;
            }
            if (!empty($mediaPath)) break;
        }

        // 3. Simpan ke tabel webhook_logs
        WebhookLog::create([
            'device_id' => $device->id,
            'sender_number' => $senderNumber,
            'message' => $text ?: ($mediaPath ? '[File Media]' : null),
            'payload' => json_encode($data)
        ]);

        // ====================================================================
        // CEK MODE LAYANAN: APAKAH LIVE CHAT DIAKTIFKAN UNTUK DEVICE INI?
        // ====================================================================
        if ($device->is_livechat_enabled) {
            // Validasi Pesan (Abaikan jika dari diri sendiri atau grup)
            if ((empty($text) && empty($mediaPath)) || $isFromMe || Str::contains($rawFrom, '@g.us') || Str::contains($chatId, '@g.us')) {
                $skipLiveChat = true;
            } else {
                $skipLiveChat = false;
            }

            if (!$skipLiveChat) {
                // --- DOWNLOAD MEDIA KE SERVER SIMAWA ---
                $finalMediaUrl = null;
                if (!empty($mediaPath)) {
                    try {
                        $cleanPath = str_replace('\\', '/', $mediaPath);

                        if (str_starts_with($cleanPath, 'http://') || str_starts_with($cleanPath, 'https://')) {
                            $downloadUrl = $cleanPath;
                        } else {
                            $downloadUrl = rtrim($gowa->getBaseUrl(), '/') . '/' . ltrim($cleanPath, '/');
                        }

                        $response = $gowa->baseClient(20)->get($downloadUrl);

                        // Jika gagal download langsung dan auto-download di GOWA mati, coba via endpoint v9 /message/{id}/download
                        if (!$response->successful() && !empty(data_get($payload, 'id'))) {
                            try {
                                $downloadMediaResp = $gowa->downloadMedia($device->gowa_session_id, data_get($payload, 'id'), $senderNumber);
                                if ($downloadMediaResp->successful()) {
                                    $fileUrl = data_get($downloadMediaResp->json(), 'results.file_url');
                                    if ($fileUrl) {
                                        $response = $gowa->baseClient(20)->get($fileUrl);
                                    }
                                }
                            } catch (\Exception $ex) {
                                // Fallback
                            }
                        }

                        if ($response->successful()) {
                            $extension = pathinfo(parse_url($cleanPath, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'bin';
                            $filename = 'media_' . time() . '_' . Str::random(5) . '.' . strtolower($extension);

                            Storage::disk('public')->put('received_media/' . $filename, $response->body());
                            $finalMediaUrl = 'received_media/' . $filename;
                        } else {
                            $finalMediaUrl = $mediaPath;
                        }
                    } catch (\Exception $e) {
                        \Log::error('Gagal download media dari GOWA: ' . $e->getMessage());
                        $finalMediaUrl = $mediaPath;
                    }
                }

                // 7. Simpan sebagai Pesan Masuk (Pesan dari Pelanggan)
                Message::create([
                    'device_id' => $device->id,
                    'user_id' => null,
                    'number' => $senderNumber,
                    'message' => $text,
                    'quoted_message' => $quotedBody, // SIMPAN KUTIPAN DI SINI
                    'direction' => 'in',
                    'status' => 'received',
                    'media_url' => $finalMediaUrl
                ]);

                // 8. UPDATE ATAU BUAT TIKET LIVE CHAT
                // Cari HANYA tiket yang statusnya masih OPEN
                $ticket = Ticket::where('device_id', $device->id)
                            ->where('number', $senderNumber)
                            ->where('status', 'open')
                            ->first();

                if ($ticket) {
                    // Jika masih ada tiket open, tandai ada pesan baru
                    $updateData = [
                        'has_new_message' => true,
                        'customer_name' => $fromName,
                    ];
                    if (empty($ticket->avatar_url)) {
                        $avatarRes = $gowa->getUserAvatar($device->gowa_session_id, $senderNumber, true);
                        if (!empty($avatarRes['results']['url'])) {
                            $updateData['avatar_url'] = $avatarRes['results']['url'];
                        }
                    }
                    $ticket->update($updateData);
                } else {
                    // =============================================================
                    // KONDISI: TIKET BARU / TIKET SEBELUMNYA SUDAH CLOSED
                    // =============================================================

                    // Ambil avatar kontak dari WhatsApp
                    $avatarUrl = null;
                    try {
                        $avatarRes = $gowa->getUserAvatar($device->gowa_session_id, $senderNumber, true);
                        $avatarUrl = $avatarRes['results']['url'] ?? null;
                    } catch (\Exception $e) {}

                    // Buat variabel pendukung pesan pembuka
                    $ticketNumber = 'LYN-' . date('ymd') . '-' . strtoupper(Str::random(4));
                    $customerName = $fromName ?: 'Bapak/Ibu';
                    $deviceName = $device->name ?: 'SIMAWA';

                    // A. Buat Tiket Baru yang bersih (Fresh) di database
                    Ticket::create([
                        'ticket_number' => $ticketNumber,
                        'device_id' => $device->id,
                        'number' => $senderNumber,
                        'customer_name' => $fromName,
                        'avatar_url' => $avatarUrl,
                        'agent_id' => null, // Bebas untuk diambil agen lain
                        'status' => 'open',
                        'has_new_message' => true
                    ]);

                    // B. Rangkai Teks Pesan Pembuka Otomatis Sesuai Request / Custom Template
                    $greetingMessage = $device->getFormattedGreetingMessage($customerName, $ticketNumber);

                    // C. Kirim Pesan Pembuka ke WhatsApp Pelanggan via GOWA
                    $sendGreeting = $gowa->sendMessage(
                        $device->gowa_session_id,
                        $senderNumber,
                        $greetingMessage
                    );

                    // D. Jika Pesan Pembuka Sukses Terkirim, Catat ke Tabel Messages
                    if ($sendGreeting->successful()) {
                        Message::create([
                            'device_id' => $device->id,
                            'user_id' => null, // null karena bot/sistem yang mengirim
                            'number' => $senderNumber,
                            'message' => $greetingMessage,
                            'direction' => 'out', // Arah keluar
                            'status' => 'sent',
                        ]);
                    } else {
                        \Log::error("Gagal mengirim Auto-Reply Greeting ke nomor: " . $senderNumber);
                    }
                }
            }
        }
        // ====================================================================
        // --- FITUR: FORWARD WEBHOOK GOWA KE SERVER LAIN (PROXY) ---
        // ====================================================================
        // Mengecek apakah device ini memiliki isian webhook_url di database
        if (!empty($device->webhook_url)) {
            try {
                // Meneruskan data mentah ($data) JSON persis seperti aslinya
                Http::timeout(5)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'X-Forwarded-By' => 'SIMAWA-PROXY'
                    ])
                    ->post($device->webhook_url, $data);

            } catch (\Exception $e) {
                // Catat error jika server tujuan mati/lambat agar Live Chat tetap aman
                \Log::error("SIMAWA Forwarding Webhook Gagal ke [{$device->webhook_url}]. Error: " . $e->getMessage());
            }
        }

        // Response akhir untuk dikembalikan ke GOWA
        return response()->json(['status' => 'success', 'message' => 'Proses SIMAWA dan Forwarding selesai']);
    }

    public function index()
    {
        // Ambil data terbaru, batasi 50 per halaman
        $logs = WebhookLog::latest()->paginate(50);
        return view('webhook_logs.index', compact('logs'));
    }

    public function clear()
    {
        WebhookLog::truncate();
        return back()->with('success', 'Semua riwayat Webhook berhasil dibersihkan.');
    }
}
