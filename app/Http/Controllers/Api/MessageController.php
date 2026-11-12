<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Message;
use App\Services\GowaService; // Pastikan import Service Anda

class MessageController extends Controller
{
    // Suntikkan GowaService ke parameter fungsi
    public function send(Request $request, GowaService $gowa)
    {
        // 1. OTORISASI BERDASARKAN API KEY DEVICE
        $apiKey = $request->header('X-API-KEY') ?? $request->api_key;
        if (!$apiKey) {
            return response()->json(['status' => 'error', 'message' => 'API Key tidak ditemukan.'], 401);
        }

        $device = Device::where('api_key', $apiKey)->first();
        if (!$device) {
            return response()->json(['status' => 'error', 'message' => 'Akses ditolak! API Key tidak valid.'], 401);
        }

        // 2. VALIDASI PAYLOAD
        $request->validate([
            'number'  => 'required|string',
            'type'    => 'required|in:text,file',
            'message' => 'nullable|string',
            'url'     => 'nullable|url',
            'file'    => 'nullable|file|max:50000',
        ]);

        if ($device->status !== 'connected') {
            return response()->json(['status' => 'error', 'message' => "Device sedang terputus (disconnected)."], 400);
        }

        if ($request->type === 'file' && !$request->hasFile('file') && !$request->url) {
            return response()->json(['status' => 'error', 'message' => 'Parameter file atau url wajib diisi.'], 400);
        }

        // FORMASI NOMOR WHATSAPP GOWA
        $formattedPhone = str_contains($request->number, '@') ? $request->number : $request->number . '@s.whatsapp.net';

        try {
            // =========================================================
            // MENGIRIM PESAN MENGGUNAKAN GOWA SERVICE
            // =========================================================
            if ($request->type === 'text') {
                // Memanggil fungsi sendMessage yang sudah ada di GowaService Anda
                $response = $gowa->sendMessage($device->gowa_session_id, $formattedPhone, $request->message);
            }
            else {
                // Siapkan file / image
                if ($request->hasFile('file')) {
                    $uploadedFile = $request->file('file');
                    $fileContents = file_get_contents($uploadedFile->getRealPath());
                    $fileName = $uploadedFile->getClientOriginalName();
                    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];

                    if (in_array($extension, $imageExtensions)) {
                        $response = $gowa->sendImage(
                            $device->gowa_session_id,
                            $formattedPhone,
                            $fileContents,
                            $fileName,
                            $request->message ?? ''
                        );
                    } else {
                        $response = $gowa->sendFile(
                            $device->gowa_session_id,
                            $formattedPhone,
                            $fileContents,
                            $fileName,
                            $request->message ?? ''
                        );
                    }
                } else {
                    $fileUrl = $request->url;
                    $fileName = basename(parse_url($fileUrl, PHP_URL_PATH)) ?: 'document.file';
                    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];

                    if (in_array($extension, $imageExtensions)) {
                        $response = $gowa->sendImageUrl(
                            $device->gowa_session_id,
                            $formattedPhone,
                            $fileUrl,
                            $request->message ?? ''
                        );
                    } else {
                        $response = $gowa->sendFileUrl(
                            $device->gowa_session_id,
                            $formattedPhone,
                            $fileUrl,
                            $request->message ?? ''
                        );
                    }
                }
            }

            // =========================================================
            // TANGANI BALASAN
            // =========================================================
            if ($response->successful()) {

                if ($device->is_livechat_enabled) {
                    $waMsgId = data_get($response->json(), 'results.message_id') ?: data_get($response->json(), 'results.id');
                    Message::create([
                        'device_id' => $device->id,
                        'user_id'   => null,
                        'number'    => preg_replace('/[^0-9]/', '', $request->number),
                        'message'   => $request->message ?? '[Mengirim Lampiran File via API]',
                        'wa_message_id' => $waMsgId,
                        'media_url' => $request->url ?? ($request->hasFile('file') ? 'uploaded_file' : null),
                        'direction' => 'out',
                        'status'    => 'sent',
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Pesan/File berhasil dikirim via SIMAWA',
                    'gowa_response' => $response->json()
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal meneruskan permintaan ke GOWA.',
                'gowa_status_code' => $response->status(),
                'gowa_error' => $response->json() ?? $response->body()
            ], $response->status());

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Koneksi ke sistem GOWA gagal: ' . $e->getMessage()], 500);
        }
    }
}
