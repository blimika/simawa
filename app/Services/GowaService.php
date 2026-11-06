<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk komunikasi dengan API WhatsApp Gateway (GOWA v9).
 * Dokumentasi OpenAPI: aldinokemal/go-whatsapp-web-multidevice (v9)
 */
class GowaService
{
    protected string $baseUrl;
    protected ?string $authUser;
    protected ?string $authPass;

    public function __construct()
    {
        $this->baseUrl  = rtrim(config('services.gowa.url', 'http://localhost:3000'), '/');
        $this->authUser = config('services.gowa.user');
        $this->authPass = config('services.gowa.pass');
    }

    /**
     * Mendapatkan URL dasar API GOWA
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Base HTTP Client dengan Basic Auth
     */
    public function baseClient(int $timeout = 15)
    {
        $client = Http::timeout($timeout);
        if ($this->authUser && $this->authPass) {
            $client = $client->withBasicAuth($this->authUser, $this->authPass);
        }
        return $client;
    }

    /**
     * Base HTTP Client dengan Basic Auth dan Header X-Device-Id
     */
    public function client(?string $deviceId = null, int $timeout = 15)
    {
        $client = $this->baseClient($timeout);
        if ($deviceId) {
            $client = $client->withHeaders(['X-Device-Id' => $deviceId]);
        }
        return $client;
    }

    /**
     * STEP 1: Daftarkan Device Slot ke GOWA secara otomatis
     * GOWA v9 Endpoint: POST /devices
     */
    public function createDevice(string $deviceId, array $options = []): ?array
    {
        try {
            $payload = array_merge([
                'device_id' => $deviceId,
            ], $options);

            $response = $this->baseClient(15)->post($this->baseUrl . '/devices', $payload);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Create Device): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mengambil daftar seluruh device yang terdaftar di GOWA
     * GOWA v9 Endpoint: GET /devices
     */
    public function getDevices(): ?array
    {
        try {
            $response = $this->baseClient(15)->get($this->baseUrl . '/devices');
            return $response->json();
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Get Devices): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mendapatkan informasi detail device tertentu
     * GOWA v9 Endpoint: GET /devices/{device_id}
     */
    public function getDevice(string $deviceId): ?array
    {
        try {
            $response = $this->client($deviceId)->get($this->baseUrl . '/devices/' . urlencode($deviceId));
            return $response->json();
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Get Device): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Cek status koneksi spesifik untuk 1 device (Mengembalikan Response object)
     * GOWA v9 Endpoint: GET /devices/{device_id}
     */
    public function getDeviceStatus(string $deviceId)
    {
        return $this->client($deviceId)->get($this->baseUrl . '/devices/' . urlencode($deviceId));
    }

    /**
     * Mengecek status login spesifik untuk 1 device
     * Mendukung endpoint v9 /devices/{device_id} dan fallback /devices/{device_id}/status atau /app/status
     */
    public function checkDeviceStatus(string $deviceId): ?array
    {
        try {
            // Coba endpoint v9 /devices/{device_id} terlebih dahulu
            $response = $this->client($deviceId)->get($this->baseUrl . '/devices/' . urlencode($deviceId));

            if ($response->successful()) {
                $data = $response->json();
                $results = $data['results'] ?? [];

                // Normalisasi status login
                $state = strtolower($results['state'] ?? '');
                $isLoggedIn = $results['is_logged_in'] ?? ($state === 'logged_in');
                $isConnected = $results['is_connected'] ?? in_array($state, ['connected', 'logged_in']);

                $data['results']['is_logged_in'] = $isLoggedIn;
                $data['results']['is_connected'] = $isConnected;

                return [
                    'http_status' => $response->status(),
                    'json_data'   => $data,
                ];
            }

            // Fallback ke /devices/{device_id}/status jika perlu
            $responseStatus = $this->client($deviceId)->get($this->baseUrl . '/devices/' . urlencode($deviceId) . '/status');
            if ($responseStatus->successful()) {
                return [
                    'http_status' => $responseStatus->status(),
                    'json_data'   => $responseStatus->json(),
                ];
            }

            // Fallback terakhir ke /app/status dengan header X-Device-Id
            $responseApp = $this->client($deviceId)->get($this->baseUrl . '/app/status');
            return [
                'http_status' => $responseApp->status(),
                'json_data'   => $responseApp->json(),
            ];
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Check Device Status): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Meminta Login / Pairing (QR Code)
     * GOWA v9 Endpoint: GET /devices/{device_id}/login (fallback: GET /app/login)
     */
    public function getLoginCode(string $deviceId): ?array
    {
        try {
            // Endpoint resmi GOWA v9 untuk device-specific QR login
            $response = $this->client($deviceId)->get($this->baseUrl . '/devices/' . urlencode($deviceId) . '/login');

            // Fallback ke /app/login jika endpoint device login mengembalikan 404
            if ($response->status() === 404) {
                $response = $this->client($deviceId)->get($this->baseUrl . '/app/login');
            }

            $decoded = json_decode($response->body(), true);
            if (is_array($decoded)) {
                return $decoded;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Get Login Code): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Meminta Pairing Code untuk login menggunakan nomor HP (Fitur GOWA v9)
     * GOWA v9 Endpoint: POST /devices/{device_id}/login/code?phone={phone}
     */
    public function getPairingCode(string $deviceId, string $phone): ?array
    {
        try {
            $response = $this->client($deviceId)->post(
                $this->baseUrl . '/devices/' . urlencode($deviceId) . '/login/code?phone=' . urlencode($phone)
            );

            if ($response->status() === 404) {
                $response = $this->client($deviceId)->get(
                    $this->baseUrl . '/app/login-with-code?phone=' . urlencode($phone)
                );
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Get Pairing Code): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Menghubungkan ulang device ke server WhatsApp
     * GOWA v9 Endpoint: POST /devices/{device_id}/reconnect
     */
    public function reconnectDevice(string $deviceId)
    {
        try {
            $response = $this->client($deviceId)->post($this->baseUrl . '/devices/' . urlencode($deviceId) . '/reconnect');

            if ($response->status() === 404) {
                $response = $this->client($deviceId)->get($this->baseUrl . '/app/reconnect');
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Reconnect Device): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Logout sesi device tanpa menghapus slot device di GOWA
     * GOWA v9 Endpoint: POST /devices/{device_id}/logout
     */
    public function logoutDevice(string $deviceId)
    {
        try {
            $response = $this->client($deviceId)->post($this->baseUrl . '/devices/' . urlencode($deviceId) . '/logout');

            if ($response->status() === 404) {
                $response = $this->client($deviceId)->get($this->baseUrl . '/app/logout');
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Logout Device): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Menghapus slot device dari server GOWA dan membersihkan sesi
     * GOWA v9 Endpoint: DELETE /devices/{device_id}
     */
    public function deleteDevice(string $deviceId): ?array
    {
        try {
            $response = $this->client($deviceId)->delete($this->baseUrl . '/devices/' . urlencode($deviceId));
            return $response->json();
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Delete Device): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mengirim Pesan Teks
     * GOWA v9 Endpoint: POST /send/message
     */
    public function sendMessage(string $deviceId, string $phone, string $message, ?string $replyMessageId = null)
    {
        $payload = [
            'phone'   => $phone,
            'message' => $message,
        ];

        if (!empty($replyMessageId)) {
            $payload['reply_message_id'] = $replyMessageId;
        }

        return $this->client($deviceId)->post($this->baseUrl . '/send/message', $payload);
    }

    /**
     * Alias method untuk sendTextMessage (mengembalikan array JSON)
     */
    public function sendTextMessage(string $deviceId, string $phone, string $message, ?string $replyMessageId = null): ?array
    {
        try {
            $response = $this->sendMessage($deviceId, $phone, $message, $replyMessageId);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Send Msg): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mengirim File / Dokumen (Multipart Form-Data)
     * GOWA v9 Endpoint: POST /send/file
     */
    public function sendFile(string $deviceId, string $phone, string $fileContents, string $fileName, string $caption = '', ?string $replyMessageId = null)
    {
        try {
            $payload = [
                'phone'   => $phone,
                'caption' => $caption,
            ];

            if (!empty($replyMessageId)) {
                $payload['reply_message_id'] = $replyMessageId;
            }

            return $this->client($deviceId)
                ->timeout(60)
                ->attach('file', $fileContents, $fileName)
                ->post($this->baseUrl . '/send/file', $payload);
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Send File): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mengirim File menggunakan URL langsung (GOWA v9 fitur file_url)
     * GOWA v9 Endpoint: POST /send/file
     */
    public function sendFileUrl(string $deviceId, string $phone, string $fileUrl, string $caption = '', ?string $replyMessageId = null)
    {
        try {
            $payload = [
                'phone'    => $phone,
                'file_url' => $fileUrl,
                'caption'  => $caption,
            ];

            if (!empty($replyMessageId)) {
                $payload['reply_message_id'] = $replyMessageId;
            }

            return $this->client($deviceId)
                ->timeout(60)
                ->post($this->baseUrl . '/send/file', $payload);
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Send File URL): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mengirim Gambar Khusus (Multipart Form-Data)
     * GOWA v9 Endpoint: POST /send/image
     */
    public function sendImage(string $deviceId, string $phone, string $imageContents, string $imageName, string $caption = '', array $options = [])
    {
        try {
            $payload = [
                'phone'   => $phone,
                'caption' => $caption,
            ];

            if (!empty($options)) {
                $payload = array_merge($payload, $options);
            }

            return $this->client($deviceId)
                ->timeout(60)
                ->attach('image', $imageContents, $imageName)
                ->post($this->baseUrl . '/send/image', $payload);
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Send Image): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mengirim Gambar menggunakan URL langsung (GOWA v9 fitur image_url)
     * GOWA v9 Endpoint: POST /send/image
     */
    public function sendImageUrl(string $deviceId, string $phone, string $imageUrl, string $caption = '', array $options = [])
    {
        try {
            $payload = [
                'phone'     => $phone,
                'image_url' => $imageUrl,
                'caption'   => $caption,
            ];

            if (!empty($options)) {
                $payload = array_merge($payload, $options);
            }

            return $this->client($deviceId)
                ->timeout(60)
                ->post($this->baseUrl . '/send/image', $payload);
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Send Image URL): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Download media dari pesan WhatsApp (Fitur GOWA v9)
     * GOWA v9 Endpoint: GET /message/{message_id}/download?phone={phone}
     */
    public function downloadMedia(string $deviceId, string $messageId, string $phone)
    {
        try {
            return $this->client($deviceId)
                ->timeout(60)
                ->get($this->baseUrl . '/message/' . urlencode($messageId) . '/download', [
                    'phone' => $phone,
                ]);
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Download Media): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mengecek status tantangan Passkey (WebAuthn) pada GOWA v9
     * GOWA v9 Endpoint: GET /app/passkey
     */
    public function getPasskeyStatus(string $deviceId): ?array
    {
        try {
            $response = $this->client($deviceId)->get($this->baseUrl . '/app/passkey');
            return $response->json();
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Get Passkey Status): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mengonfirmasi kode pairing Passkey ke GOWA v9
     * GOWA v9 Endpoint: POST /app/passkey/confirm
     */
    public function confirmPasskey(string $deviceId)
    {
        try {
            return $this->client($deviceId)->post($this->baseUrl . '/app/passkey/confirm');
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Confirm Passkey): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mengirimkan WebAuthn assertion untuk Passkey pairing ke GOWA v9
     * GOWA v9 Endpoint: POST /app/passkey/response
     */
    public function submitPasskeyResponse(string $deviceId, array $assertion)
    {
        try {
            return $this->client($deviceId)->post($this->baseUrl . '/app/passkey/response', $assertion);
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Submit Passkey Response): ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Mengambil daftar kontak WhatsApp yang tersimpan di akun WhatsApp device
     * GOWA v9 Endpoint: GET /user/my/contacts
     * Header: X-Device-Id: {device_id}
     */
    public function getMyContacts(string $deviceId): ?array
    {
        try {
            $response = $this->client($deviceId)->get($this->baseUrl . '/user/my/contacts');
            return $response->json();
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Get My Contacts): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mengambil URL avatar profil WhatsApp untuk nomor tertentu
     * GOWA v9 Endpoint: GET /user/avatar?phone={phone}&is_preview={preview}
     */
    public function getUserAvatar(string $deviceId, string $phone, bool $isPreview = true): ?array
    {
        try {
            $response = $this->client($deviceId)->get($this->baseUrl . '/user/avatar', [
                'phone' => $phone,
                'is_preview' => $isPreview ? 'true' : 'false',
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Get User Avatar): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mengambil profil lengkap akun WhatsApp yang sedang terhubung pada device
     * Menggabungkan informasi dari /devices/{device_id} dan /user/avatar
     */
    public function getConnectedWhatsAppProfile(string $deviceId): ?array
    {
        try {
            $deviceInfo = $this->getDevice($deviceId);
            $results = $deviceInfo['results'] ?? [];

            $state = strtolower($results['state'] ?? '');
            $isLoggedIn = in_array($state, ['logged_in', 'connected']);

            if (!$isLoggedIn && empty($results['jid'])) {
                return null;
            }

            $displayName = $results['display_name'] ?? null;
            $jid = $results['jid'] ?? '';
            $phone = null;
            if ($jid) {
                $phone = explode('@', explode(':', $jid)[0])[0];
            }

            $avatarUrl = null;
            if ($phone) {
                $avatarRes = $this->getUserAvatar($deviceId, $phone, true);
                $avatarUrl = $avatarRes['results']['url'] ?? null;
            }

            return [
                'name' => $displayName ?: $phone,
                'phone' => $phone,
                'jid' => $jid,
                'avatar' => $avatarUrl,
                'state' => $state,
                'is_logged_in' => $isLoggedIn,
            ];
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Get Connected WhatsApp Profile): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mengambil konfigurasi webhook spesifik device
     * GOWA v9 Endpoint: GET /devices/{device_id}/webhook
     */
    public function getDeviceWebhook(string $deviceId): ?array
    {
        try {
            $response = $this->client($deviceId)->get($this->baseUrl . '/devices/' . urlencode($deviceId) . '/webhook');
            return $response->json();
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Get Device Webhook): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mengatur konfigurasi webhook spesifik device
     * GOWA v9 Endpoint: PATCH /devices/{device_id}/webhook
     */
    public function setDeviceWebhook(string $deviceId, string $webhookUrl, ?string $secret = null, ?string $events = null, bool $insecureSkipVerify = false): ?array
    {
        try {
            $payload = [
                'webhook_url' => $webhookUrl,
            ];

            if ($secret !== null) {
                $payload['webhook_secret'] = $secret;
            }
            if ($events !== null) {
                $payload['webhook_events'] = $events;
            }
            if ($insecureSkipVerify) {
                $payload['webhook_insecure_skip_verify'] = true;
            }

            $response = $this->client($deviceId)->patch($this->baseUrl . '/devices/' . urlencode($deviceId) . '/webhook', $payload);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Set Device Webhook): ' . $e->getMessage());
            return null;
        }
    }
}
