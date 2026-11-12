<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WebhookLog;
use App\Models\Device;
use App\Models\Message;
use App\Models\Ticket;
use App\Models\Phonebook;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
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
            if ($device) {
                WebhookLog::create([
                    'device_id' => $device->id,
                    'sender_number' => 'SYSTEM',
                    'message' => '[Device Logged Out] ' . data_get($data, 'payload.reason', 'Terputus dari WhatsApp'),
                    'payload' => $data
                ]);
            }
            return response()->json(['status' => 'success', 'message' => 'Device logged out handled']);
        }

        // 1. Abaikan jika ini bukan event pesan masuk
        if ($event !== 'message') {
            if ($device) {
                WebhookLog::create([
                    'device_id' => $device->id,
                    'sender_number' => 'SYSTEM',
                    'message' => "[Event: {$event}]",
                    'payload' => $data
                ]);
            }
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
        $originalDocName = null;
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
                $originalDocName = $mediaData['file_name'] ?? $mediaData['filename'] ?? null;
            } elseif (is_string($mediaData)) {
                $mediaPath = $mediaData;
                $originalDocName = basename($mediaData);
            }
            if (!empty($mediaPath)) break;
        }

        // Tangkap teks caption jika body kosong pada pesan media
        if (empty($text)) {
            $text = data_get($payload, 'caption') 
                ?: data_get($payload, 'image.caption') 
                ?: data_get($payload, 'document.caption') 
                ?: data_get($payload, 'video.caption') 
                ?: '';
        }

        // 3. Simpan ke tabel webhook_logs
        WebhookLog::create([
            'device_id' => $device->id,
            'sender_number' => $senderNumber,
            'message' => $text ?: ($mediaPath ? '[File Media]' : null),
            'payload' => $data
        ]);

        // ====================================================================
        // CEK MODE LAYANAN: APAKAH LIVE CHAT DIAKTIFKAN UNTUK DEVICE INI?
        // ====================================================================
        if ($device->is_livechat_enabled) {
            // Validasi Pesan: Abaikan jika bukan teks/media atau pesan berasal dari grup WhatsApp (@g.us)
            if ((empty($text) && empty($mediaPath)) || Str::contains($rawFrom, '@g.us') || Str::contains($chatId, '@g.us')) {
                $skipLiveChat = true;
            } else {
                $skipLiveChat = false;
            }

            if (!$skipLiveChat) {
                // Tentukan nomor pelanggan (customer number):
                // - Jika is_from_me = true (pesan keluar dikirim via WA Web/HP), nomor pelanggan ada di chatId
                // - Jika is_from_me = false (pesan masuk dari pelanggan), nomor pelanggan ada di rawFrom (senderNumber)
                $targetCustomerNumber = $isFromMe
                    ? (!empty($chatId) ? explode('@', explode(':', $chatId)[0])[0] : $senderNumber)
                    : $senderNumber;

                $targetCustomerNumber = preg_replace('/[^0-9]/', '', $targetCustomerNumber);
                if (str_starts_with($targetCustomerNumber, '0')) {
                    $targetCustomerNumber = '62' . substr($targetCustomerNumber, 1);
                }

                // Cegah pemrosesan duplikat berdasarkan ID unik pesan WhatsApp
                $messageId = data_get($payload, 'id');
                if (!empty($messageId)) {
                    $cacheKey = "gowa_msg_processed_{$messageId}";
                    if (Cache::has($cacheKey)) {
                        return response()->json(['status' => 'ignored', 'reason' => 'Pesan sudah diproses sebelumnya']);
                    }
                    Cache::put($cacheKey, true, now()->addMinutes(10));
                }

                // Tautkan ke pesan lokal yang dibalas jika ada replied_to_id dari WhatsApp
                $repliedDbMsg = null;
                if (!empty($repliedToId)) {
                    $repliedDbMsg = Message::where('device_id', $device->id)
                        ->where('wa_message_id', $repliedToId)
                        ->first();
                    if ($repliedDbMsg && ($quotedBody === '📷 [Membalas Lampiran / File Media]' || empty($quotedBody)) && !empty($repliedDbMsg->message)) {
                        $quotedBody = $repliedDbMsg->message;
                    }
                }

                if ($isFromMe) {
                    // =============================================================
                    // KONDISI: PESAN KELUAR (Dikirim dari WhatsApp Web / Aplikasi WA HP)
                    // =============================================================

                    // Cek apakah pesan ini baru saja dikirim oleh SIMAWA Live Chat (dalam 30 detik terakhir)
                    // Jika dikirim lewat web SIMAWA, maka sudah tercatat di tabel messages
                    $isSentBySimawa = Message::where('device_id', $device->id)
                        ->where('number', $targetCustomerNumber)
                        ->where('direction', 'out')
                        ->where('created_at', '>=', now()->subSeconds(30))
                        ->where(function ($q) use ($text, $mediaPath) {
                            if (!empty($text)) {
                                $q->where('message', $text)
                                  ->orWhere('message', 'like', $text . "\n\n*(%")
                                  ->orWhere('message', 'like', "%" . $text . "%");
                            } elseif (!empty($mediaPath)) {
                                $q->whereNotNull('media_url');
                            }
                        })
                        ->exists();

                    if ($isSentBySimawa) {
                        // Sudah dicatat oleh SIMAWA saat pengiriman via Web UI, abaikan webhook echo ini
                        return response()->json(['status' => 'success', 'message' => 'Pesan keluar SIMAWA sudah dicatat sebelumnya']);
                    }

                    // Download media jika pesan menyertakan file/gambar
                    $finalMediaUrl = $this->downloadMediaFile($gowa, $device, $payload, $mediaPath, $originalDocName, $targetCustomerNumber);

                    // Cari tiket yang sedang OPEN untuk nomor tujuan ini
                    $ticket = Ticket::where('device_id', $device->id)
                                ->where('number', $targetCustomerNumber)
                                ->where('status', 'open')
                                ->first();

                    // Simpan sebagai Pesan Keluar ke tabel messages
                    Message::create([
                        'device_id' => $device->id,
                        'user_id' => $ticket?->agent_id ?: $device->user_id,
                        'number' => $targetCustomerNumber,
                        'message' => $text,
                        'quoted_message' => $quotedBody,
                        'wa_message_id' => $messageId,
                        'reply_to_id' => $repliedDbMsg?->id,
                        'direction' => 'out',
                        'status' => 'sent',
                        'media_url' => $finalMediaUrl
                    ]);

                    // Update tiket atau buat baru jika belum ada
                    if ($ticket) {
                        $updateData = [];
                        if (empty($ticket->agent_id) && !empty($device->user_id)) {
                            $updateData['agent_id'] = $device->user_id;
                        }
                        if (empty($ticket->avatar_url)) {
                            try {
                                $avatarRes = $gowa->getUserAvatar($device->gowa_session_id, $targetCustomerNumber, true);
                                if (!empty($avatarRes['results']['url'])) {
                                    $updateData['avatar_url'] = $avatarRes['results']['url'];
                                }
                            } catch (\Exception $e) {}
                        }
                        if (!empty($updateData)) {
                            $ticket->update($updateData);
                        } else {
                            $ticket->touch();
                        }
                    } else {
                        // Belum ada tiket open: buat tiket baru agar obrolan muncul di Live Chat
                        $avatarUrl = null;
                        try {
                            $avatarRes = $gowa->getUserAvatar($device->gowa_session_id, $targetCustomerNumber, true);
                            $avatarUrl = $avatarRes['results']['url'] ?? null;
                        } catch (\Exception $e) {}

                        // Cek nama dari kontak phonebook
                        $contact = Phonebook::where('number', $targetCustomerNumber)->first();
                        $custName = $contact?->name;
                        if (empty($custName)) {
                            $oldTicket = Ticket::where('device_id', $device->id)
                                ->where('number', $targetCustomerNumber)
                                ->latest()
                                ->first();
                            $custName = $oldTicket?->customer_name ?: null;
                        }

                        Ticket::create([
                            'ticket_number' => 'LYN-' . date('ymd') . '-' . strtoupper(Str::random(4)),
                            'device_id' => $device->id,
                            'number' => $targetCustomerNumber,
                            'customer_name' => $custName,
                            'avatar_url' => $avatarUrl,
                            'agent_id' => $device->user_id,
                            'status' => 'open',
                            'has_new_message' => false,
                        ]);
                    }

                } else {
                    // =============================================================
                    // KONDISI: PESAN MASUK (Dari Pelanggan)
                    // =============================================================

                    // Download media jika pesan menyertakan file/gambar
                    $finalMediaUrl = $this->downloadMediaFile($gowa, $device, $payload, $mediaPath, $originalDocName, $targetCustomerNumber);

                    // Simpan sebagai Pesan Masuk
                    Message::create([
                        'device_id' => $device->id,
                        'user_id' => null,
                        'number' => $targetCustomerNumber,
                        'message' => $text,
                        'quoted_message' => $quotedBody,
                        'wa_message_id' => $messageId,
                        'reply_to_id' => $repliedDbMsg?->id,
                        'direction' => 'in',
                        'status' => 'received',
                        'media_url' => $finalMediaUrl
                    ]);

                    // UPDATE ATAU BUAT TIKET LIVE CHAT
                    $ticket = Ticket::where('device_id', $device->id)
                                ->where('number', $targetCustomerNumber)
                                ->where('status', 'open')
                                ->first();

                    if ($ticket) {
                        // Jika masih ada tiket open, tandai ada pesan baru
                        $updateData = [
                            'has_new_message' => true,
                            'customer_name' => $fromName ?: $ticket->customer_name,
                        ];
                        if (empty($ticket->avatar_url)) {
                            try {
                                $avatarRes = $gowa->getUserAvatar($device->gowa_session_id, $targetCustomerNumber, true);
                                if (!empty($avatarRes['results']['url'])) {
                                    $updateData['avatar_url'] = $avatarRes['results']['url'];
                                }
                            } catch (\Exception $e) {}
                        }
                        $ticket->update($updateData);
                    } else {
                        // KONDISI: TIKET BARU / TIKET SEBELUMNYA SUDAH CLOSED
                        $avatarUrl = null;
                        try {
                            $avatarRes = $gowa->getUserAvatar($device->gowa_session_id, $targetCustomerNumber, true);
                            $avatarUrl = $avatarRes['results']['url'] ?? null;
                        } catch (\Exception $e) {}

                        $ticketNumber = 'LYN-' . date('ymd') . '-' . strtoupper(Str::random(4));
                        $customerName = $fromName ?: 'Bapak/Ibu';

                        // Buat Tiket Baru di database
                        Ticket::create([
                            'ticket_number' => $ticketNumber,
                            'device_id' => $device->id,
                            'number' => $targetCustomerNumber,
                            'customer_name' => $fromName,
                            'avatar_url' => $avatarUrl,
                            'agent_id' => null,
                            'status' => 'open',
                            'has_new_message' => true
                        ]);

                        // Kirim Salam Pembuka Otomatis (Hanya jika fitur Salam Pembuka diaktifkan untuk device ini)
                        if ($device->isGreetingEnabled()) {
                            $greetingMessage = $device->getFormattedGreetingMessage($customerName, $ticketNumber);

                            $sendGreeting = $gowa->sendMessage(
                                $device->gowa_session_id,
                                $targetCustomerNumber,
                                $greetingMessage
                            );

                            if ($sendGreeting->successful()) {
                                $greetingWaId = data_get($sendGreeting->json(), 'results.message_id')
                                    ?: data_get($sendGreeting->json(), 'results.id');

                                Message::create([
                                    'device_id' => $device->id,
                                    'user_id' => null,
                                    'number' => $targetCustomerNumber,
                                    'message' => $greetingMessage,
                                    'wa_message_id' => $greetingWaId,
                                    'direction' => 'out',
                                    'status' => 'sent',
                                ]);
                            } else {
                                \Log::error("Gagal mengirim Auto-Reply Greeting ke nomor: " . $targetCustomerNumber);
                            }
                        }
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

    /**
     * Tampilkan Riwayat Webhook dengan Filter, Pencarian, & Statistik
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Ambil daftar device yang berhak diakses oleh pengguna
        $devicesQuery = Device::query();
        if ($user->isSuperadmin()) {
            // Superadmin dapat melihat seluruh device
        } elseif ($user->isAdmin()) {
            $devicesQuery->where('user_id', $user->id);
        } else {
            // Agen melihat device milik admin/parent atau handledDevices
            $handled = $user->handledDevices()->pluck('devices.id');
            if ($handled->isNotEmpty()) {
                $devicesQuery->whereIn('id', $handled);
            } else {
                $devicesQuery->where('user_id', $user->parent_id);
            }
        }
        $devices = $devicesQuery->orderBy('name', 'asc')->get();
        $allowedDeviceIds = $devices->pluck('id')->toArray();

        // 2. Siapkan Query WebhookLog dengan Eager Loading
        $query = WebhookLog::with('device');

        if (!$user->isSuperadmin()) {
            $query->whereIn('device_id', $allowedDeviceIds);
        }

        // Filter Spesifik Device
        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        // Filter Event GOWA (message, device.logged_out, dll)
        if ($request->filled('event')) {
            $eventFilter = $request->event;
            if ($eventFilter === 'message') {
                $query->where(function ($q) {
                    $q->where('payload', 'like', '%"event":"message"%')
                      ->orWhere('payload', 'like', '%"event": "message"%')
                      ->orWhereNull('payload->event');
                });
            } elseif ($eventFilter === 'device.logged_out') {
                $query->where(function ($q) {
                    $q->where('payload', 'like', '%"event":"device.logged_out"%')
                      ->orWhere('payload', 'like', '%"event": "device.logged_out"%');
                });
            } else {
                $query->where('payload', 'like', '%"event":"' . $eventFilter . '"%')
                      ->orWhere('payload', 'like', '%"event": "' . $eventFilter . '"%');
            }
        }

        // Filter Rentang Tanggal
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case '7days':
                    $query->where('created_at', '>=', now()->subDays(7));
                    break;
                case '30days':
                    $query->where('created_at', '>=', now()->subDays(30));
                    break;
            }
        }

        // Pencarian Teks (Nomor pengirim, isi pesan, atau isi payload)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('sender_number', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhere('payload', 'like', "%{$search}%");
            });
        }

        // 3. Statistik Ringkasan untuk Card Widget
        $baseStatQuery = WebhookLog::query();
        if (!$user->isSuperadmin()) {
            $baseStatQuery->whereIn('device_id', $allowedDeviceIds);
        }
        if ($request->filled('device_id')) {
            $baseStatQuery->where('device_id', $request->device_id);
        }

        $stats = [
            'total' => (clone $baseStatQuery)->count(),
            'today' => (clone $baseStatQuery)->whereDate('created_at', today())->count(),
            'messages' => (clone $baseStatQuery)->where(function ($q) {
                $q->where('payload', 'like', '%"event":"message"%')
                  ->orWhere('payload', 'like', '%"event": "message"%')
                  ->orWhereNull('payload->event');
            })->count(),
            'events' => (clone $baseStatQuery)->where(function ($q) {
                $q->where('payload', 'like', '%"event":"device.logged_out"%')
                  ->orWhere('payload', 'like', '%"event": "device.logged_out"%');
            })->count(),
        ];

        // Ambil data dengan pagination 25 baris & simpan query string
        $logs = $query->latest()->paginate(25)->withQueryString();

        return view('webhook_logs.index', compact('logs', 'devices', 'stats'));
    }

    /**
     * Dapatkan Detail JSON Payload Sebuah Log
     */
    public function show(WebhookLog $webhookLog)
    {
        $webhookLog->load('device');

        return response()->json([
            'id' => $webhookLog->id,
            'device_id' => $webhookLog->device_id,
            'device_name' => $webhookLog->device?->name ?? 'Device #'.$webhookLog->device_id,
            'device_phone' => $webhookLog->device?->phone_number,
            'session_id' => $webhookLog->session_id,
            'event' => $webhookLog->event,
            'sender_number' => $webhookLog->sender_number,
            'sender_name' => $webhookLog->sender_display_name,
            'message' => $webhookLog->message,
            'media_type' => $webhookLog->media_type,
            'media_info' => $webhookLog->media_info,
            'created_at' => $webhookLog->created_at->format('d M Y H:i:s'),
            'created_at_relative' => $webhookLog->created_at->diffForHumans(),
            'payload' => $webhookLog->payload_data,
        ]);
    }

    /**
     * Hapus Satu Baris Riwayat Webhook Tertentu
     */
    public function destroy(WebhookLog $webhookLog)
    {
        $user = auth()->user();
        if (!$user->isSuperadmin()) {
            $ownerId = $user->getOwnerId();
            if ($webhookLog->device && $webhookLog->device->user_id !== $ownerId) {
                abort(403, 'Anda tidak memiliki hak untuk menghapus riwayat webhook ini.');
            }
        }

        $webhookLog->delete();

        return back()->with('success', 'Riwayat Webhook berhasil dihapus.');
    }

    /**
     * Bersihkan Seluruh Riwayat Webhook (atau Filter Device)
     */
    public function clear(Request $request)
    {
        $user = auth()->user();

        // Jika superadmin dan tidak ada filter device_id -> truncate penuh
        if ($user->isSuperadmin() && !$request->filled('device_id')) {
            WebhookLog::truncate();
            return back()->with('success', 'Semua riwayat Webhook berhasil dibersihkan.');
        }

        $query = WebhookLog::query();
        if (!$user->isSuperadmin()) {
            $ownerId = $user->getOwnerId();
            $allowedDeviceIds = Device::where('user_id', $ownerId)->pluck('id');
            $query->whereIn('device_id', $allowedDeviceIds);
        }

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        $count = $query->count();
        $query->delete();

        return back()->with('success', "{$count} riwayat Webhook berhasil dibersihkan.");
    }

    /**
     * Download file media dari server GOWA dan simpan ke storage publik SIMAWA
     */
    private function downloadMediaFile($gowa, $device, $payload, ?string $mediaPath, ?string $originalDocName, string $targetNumber): ?string
    {
        if (empty($mediaPath)) {
            return null;
        }

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
                    $downloadMediaResp = $gowa->downloadMedia($device->gowa_session_id, data_get($payload, 'id'), $targetNumber);
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
                $cleanPathOnly = parse_url($cleanPath, PHP_URL_PATH);
                $extension = pathinfo($cleanPathOnly, PATHINFO_EXTENSION);
                if (empty($extension) && $originalDocName) {
                    $extension = pathinfo($originalDocName, PATHINFO_EXTENSION);
                }
                if (empty($extension)) {
                    $extension = 'bin';
                }

                $safeDocName = '';
                if ($originalDocName) {
                    $base = Str::slug(pathinfo($originalDocName, PATHINFO_FILENAME));
                    if ($base) {
                        $safeDocName = '_' . substr($base, 0, 30);
                    }
                }

                $filename = 'media_' . time() . '_' . Str::random(5) . $safeDocName . '.' . strtolower($extension);

                Storage::disk('public')->put('received_media/' . $filename, $response->body());
                return 'received_media/' . $filename;
            }

            return $mediaPath;
        } catch (\Exception $e) {
            \Log::error('Gagal download media dari GOWA: ' . $e->getMessage());
            return $mediaPath;
        }
    }
}
