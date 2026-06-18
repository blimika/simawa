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
        // =======================================================
        // TAMBAHKAN 2 BARIS INI UNTUK MENGINTIP ISI ASLI DARI GOWA
        \Log::info('--- MENGINTIP ISI WEBHOOK GOWA ---');
        \Log::info(json_encode($data, JSON_PRETTY_PRINT));
        // =======================================================
        // 1. Abaikan jika ini bukan event pesan masuk
        if ($request->input('event') !== 'message') {
            return response()->json(['status' => 'ignored', 'reason' => 'Bukan event message']);
        }

        $payload = $request->input('payload');

        // 2. Ekstrak Nomor Device Penerima (Dari 628xxx@s.whatsapp.net menjadi 628xxx)
        $rawDeviceId = $request->input('device_id');
        $deviceNumber = $rawDeviceId ? explode('@', $rawDeviceId)[0] : null;

        // 3. Ekstrak Nomor Pengirim, Isi Pesan, dan Media
        $rawFrom = data_get($payload, 'from');
        $senderNumber = $rawFrom ? explode('@', $rawFrom)[0] : 'unknown';
        $fromName = data_get($payload, 'from_name');
        $text = data_get($payload, 'body') ?? '';
        $isFromMe = data_get($payload, 'is_from_me', false);

       // --- TANGKAP KUTIPAN PESAN (QUOTE REPLY) ---
        $quotedBody = data_get($payload, 'quoted_body');
        $repliedToId = data_get($payload, 'replied_to_id'); // Tangkap juga ID balasannya

        // LOGIKA CERDAS: Jika ada ID balasan tapi teks kutipannya kosong,
        // berarti pelanggan merespons/mengutip sebuah gambar atau dokumen.
        if (empty($quotedBody) && !empty($repliedToId)) {
            $quotedBody = '📷 [Membalas Lampiran / File Media]';
        }

        // Ekstrak Media Gambar / Dokumen
        $rawImage = data_get($payload, 'image');
        $rawDocument = data_get($payload, 'document');
        $mediaPath = null;

        if (is_array($rawImage) && isset($rawImage['path'])) {
            $mediaPath = $rawImage['path'];
        } elseif (is_string($rawImage)) {
            $mediaPath = $rawImage;
        } elseif (is_array($rawDocument) && isset($rawDocument['path'])) {
            $mediaPath = $rawDocument['path'];
        } elseif (is_string($rawDocument)) {
            $mediaPath = $rawDocument;
        }

        // 4. Cari Device di SIMAWA berdasarkan NOMOR phone_number
        $device = Device::where('phone_number', $deviceNumber)->first();

        if (!$device) {
            \Log::error("Webhook GOWA: Device dengan nomor '$deviceNumber' tidak ditemukan di database SIMAWA.");
            return response()->json(['status' => 'error', 'message' => 'Device not found'], 404);
        }

        // 5. Simpan ke tabel webhook_logs
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
            /*
            if ((empty($text) && empty($mediaPath)) || $isFromMe || Str::contains($rawFrom, '@g.us')) {
                return response()->json(['status' => 'ignored', 'reason' => 'Pesan dari diri sendiri, grup, atau kosong']);
            } */

            // 6. Validasi Pesan (Abaikan jika dari diri sendiri atau grup)
            if ((empty($text) && empty($mediaPath)) || $isFromMe || Str::contains($rawFrom, '@g.us')) {
                // Return di sini kita hilangkan agar lanjut ke Webhook Forwarding di bawah
                $skipLiveChat = true;
            } else {
                $skipLiveChat = false;
            }

            if (!$skipLiveChat) {
                // --- DOWNLOAD MEDIA KE SERVER SIMAWA ---
                $finalMediaUrl = null;
                if (!empty($mediaPath)) {
                    try {
                        $gowaBaseUrl = env('GOWA_API_URL', 'http://localhost:5271');
                        $cleanPath = str_replace('\\', '/', $mediaPath);
                        $downloadUrl = rtrim($gowaBaseUrl, '/') . '/' . ltrim($cleanPath, '/');

                        $gowaUser = env('GOWA_BASIC_USER', 'kota');
                        $gowaPass = env('GOWA_BASIC_PASS', 'kota');

                        $response = Http::withBasicAuth($gowaUser, $gowaPass)->get($downloadUrl);

                        if ($response->successful()) {
                            $extension = pathinfo($cleanPath, PATHINFO_EXTENSION) ?: 'bin';
                            $filename = 'media_' . time() . '_' . Str::random(5) . '.' . strtolower($extension);

                            Storage::disk('public')->put('received_media/' . $filename, $response->body());
                            $finalMediaUrl = 'received_media/' . $filename;
                        } else {
                            $finalMediaUrl = $mediaPath;
                        }
                    } catch (\Exception $e) {
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
                    // Jika masih ada tiket open, cukup tandai ada pesan baru
                    $ticket->update([
                        'has_new_message' => true,
                        'customer_name' => $fromName
                    ]);
                } else {
                    // =============================================================
                    // KONDISI: TIKET BARU / TIKET SEBELUMNYA SUDAH CLOSED
                    // =============================================================

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
                        'agent_id' => null, // Bebas untuk diambil agen lain
                        'status' => 'open',
                        'has_new_message' => true
                    ]);

                    // B. Rangkai Teks Pesan Pembuka Otomatis Sesuai Request
                    $greetingMessage = "Halo Bapak/Ibu {$customerName},\n"
                                    . "Terima kasih telah menghubungi layanan WhatsApp {$deviceName}.\n\n"
                                    . "Pesan Bapak/Ibu telah kami terima dengan\n"
                                    . "nomor tiket: {$ticketNumber}.\n\n"
                                    . "Operator kami akan menindaklanjuti pesan Bapak/Ibu pada jam layanan.\n\n"
                                    . "Terima kasih.";

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
        }        // ====================================================================
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
}
