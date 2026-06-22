<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GowaService
{
    protected $baseUrl;
    protected $authUser;
    protected $authPass;

    public function __construct()
    {
        $this->baseUrl  = config('services.gowa.url');
        $this->authUser = config('services.gowa.user');
        $this->authPass = config('services.gowa.pass');
    }
    /**
     * STEP 1: Daftarkan Device ke GOWA secara otomatis saat Admin input
     * Endpoint: POST /devices
     */
    public function createDevice($deviceId)
    {
        try {
            // Http facade Laravel otomatis mengirim sebagai raw JSON (application/json)
            // ketika kita memberikan array ke method post()
            $response = Http::withBasicAuth($this->authUser, $this->authPass)
                ->timeout(10)
                ->post($this->baseUrl . '/devices', [
                    'device_id' => $deviceId
                ]);

            return $response->json();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('GOWA API Error (Create Device): ' . $e->getMessage());
            return null;
        }
    }
    public function getDevices()
    {
        try {
            // Karena ini GET global, tidak butuh header X-Device-Id
            $response = Http::withBasicAuth($this->authUser, $this->authPass)
                ->timeout(10)
                ->get($this->baseUrl . '/devices');

            return $response->json();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('GOWA API Error (Get Devices): ' . $e->getMessage());
            return null;
        }
    }
    /**
     * Base HTTP Client dengan Auth dan Header X-Device-Id
     */
    protected function client($deviceId)
    {
        return Http::withBasicAuth($this->authUser, $this->authPass)
                   ->withHeaders(['X-Device-Id' => $deviceId])
                   ->timeout(15);
    }

    /**
     * Meminta Login / Pairing (QR Code)
     * Endpoint asumsikan POST /app/login
     */
    public function getLoginCode($deviceId)
    {
        try {
            $response = $this->client($deviceId)->get($this->baseUrl . '/app/login');

            // PAKSA TERJEMAHKAN TEKS RAW MENJADI ARRAY JSON
            // Ini akan mengatasi masalah null jika GOWA tidak mengirimkan header yang tepat
            $decodedJson = json_decode($response->body(), true);

            return $decodedJson;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('GOWA API Error (Get Login): ' . $e->getMessage());
            return null;
        }
    }
    /**
     * Menghapus session di server GOWA
     * Endpoint: DELETE /app/logout atau /devices tergantung versi
     * Kita gunakan DELETE /devices/{id} sesuai standar REST
     */
    public function deleteDevice($deviceId)
    {
        try {
            $response = $this->client($deviceId)->delete($this->baseUrl . '/devices/' . $deviceId);
            return $response->json();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('GOWA API Error (Delete): ' . $e->getMessage());
            return null;
        }
    }
    /**
     * Mengecek status login spesifik untuk 1 device
     * Endpoint: GET /app/status
     */
    public function checkDeviceStatus($deviceId)
    {
        try {
            // Gunakan method client() agar header X-Device-Id otomatis ikut terkirim
            $response = $this->client($deviceId)->get($this->baseUrl . '/app/status');

            return [
                'http_status' => $response->status(),
                'json_data'   => $response->json()
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('GOWA API Error (App Status): ' . $e->getMessage());
            return null;
        }
    }
    /**
     * Mengirim Pesan Teks
     */
    public function sendTextMessage($deviceId, $phone, $message)
    {
        try {
            $response = $this->client($deviceId)->post($this->baseUrl . '/send/message', [
                'phone'   => $phone,
                'message' => $message,
                'isGroup' => false
            ]);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('GOWA API Error (Send Msg): ' . $e->getMessage());
            return null;
        }
    }
    /**
     * Cek status device berdasarkan Session ID
     */
    public function getDeviceStatus_lama($sessionId)
    {
        // Menggabungkan $this->baseUrl dengan endpoint devices
        //return $this->client($sessionId)->get($this->baseUrl . '/devices/' . $sessionId);
        try {
            $response = $this->client($sessionId)->get($this->baseUrl . '/devices/' . $sessionId);

            // PAKSA TERJEMAHKAN TEKS RAW MENJADI ARRAY JSON
            // Ini akan mengatasi masalah null jika GOWA tidak mengirimkan header yang tepat
            // $decodedJson = json_decode($response->body(), true);
            //return $decodedJson;
            return $response->json();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('GOWA API Error (Get Device Status): ' . $e->getMessage());
            return null;
        }
    }
    public function getDeviceStatus($sessionId)
    {
        // JANGAN tambahkan ->json() di sini
        return $this->client($sessionId)->get($this->baseUrl . '/devices/' . $sessionId);
    }
    public function sendMessage($sessionId, $number, $message)
    {
        return $this->client($sessionId)->post($this->baseUrl . '/send/message', [
            'phone' => $number,
            'isGroup' => false,
            'message' => $message
        ]);
    }
    /**
     * Mengirim File / Gambar / Dokumen (Multipart Form-Data)
     */
    public function sendFile($sessionId, $phone, $fileContents, $fileName, $caption = '')
    {
        try {
            // Gunakan fungsi client() bawaan Anda untuk menyematkan Auth & Header otomatis
            return $this->client($sessionId)
                ->timeout(60) // Perpanjang timeout khusus untuk upload file
                ->attach('file', $fileContents, $fileName)
                ->post($this->baseUrl . '/send/file', [
                    'phone' => $phone,
                    'caption' => $caption
                ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('GOWA API Error (Send File): ' . $e->getMessage());
            throw $e; // Lempar error ke Controller agar bisa ditangkap oleh API response
        }
    }
    /**
     * Mengirim Gambar Khusus (Multipart Form-Data)
     * Endpoint: /send/image
     */
    public function sendImage($sessionId, $phone, $imageContents, $imageName, $caption = '', $options = [])
    {
        try {
            // Base payload sesuai dokumentasi Swagger
            $payload = [
                'phone'   => $phone,
                'caption' => $caption,
            ];

            // Menggabungkan parameter opsional seperti compress, view_once, dll
            if (!empty($options)) {
                $payload = array_merge($payload, $options);
            }

            return $this->client($sessionId)
                ->timeout(60)
                // PERHATIKAN: Key di sini adalah 'image', sesuai dengan spec GOWA
                ->attach('image', $imageContents, $imageName)
                ->post($this->baseUrl . '/send/image', $payload);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('GOWA API Error (Send Image): ' . $e->getMessage());
            throw $e;
        }
    }
}
