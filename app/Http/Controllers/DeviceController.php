<?php

namespace App\Http\Controllers;
use App\Models\Device;
use Illuminate\Http\Request;
use App\Services\GowaService; // Import Service yang baru dibuat
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rules;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GowaService $gowa)
    {
        $user = auth()->user();

        $query = Device::query();

        if ($user->isSuperadmin()) {
            // Superadmin lihat semua
            $devices = $query->latest()->paginate(10);
        } elseif ($user->isAdmin()) {
            // Admin lihat miliknya sendiri
            $devices = $query->where('user_id', $user->id)->latest()->paginate(10);
        } else {
            // Agent lihat milik Admin-nya (parent_id)
            $devices = $query->where('user_id', $user->parent_id)->latest()->paginate(10);
        }

        // Sinkronisasi info profil WhatsApp (nama & avatar) untuk setiap device yang terhubung
        foreach ($devices as $device) {
            if ($device->status === 'connected') {
                $cacheKey = "device_wa_profile_{$device->id}";
                $profile = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($gowa, $device) {
                    return $gowa->getConnectedWhatsAppProfile($device->gowa_session_id);
                });

                if ($profile) {
                    $needsUpdate = false;
                    $updateData = [];

                    if (!empty($profile['name']) && $device->whatsapp_name !== $profile['name']) {
                        $updateData['whatsapp_name'] = $profile['name'];
                        $device->whatsapp_name = $profile['name'];
                        $needsUpdate = true;
                    }

                    if (!empty($profile['avatar']) && $device->whatsapp_avatar !== $profile['avatar']) {
                        $updateData['whatsapp_avatar'] = $profile['avatar'];
                        $device->whatsapp_avatar = $profile['avatar'];
                        $needsUpdate = true;
                    }

                    if (!empty($profile['phone']) && $device->phone_number !== $profile['phone']) {
                        $updateData['phone_number'] = $profile['phone'];
                        $device->phone_number = $profile['phone'];
                        $needsUpdate = true;
                    }

                    if ($needsUpdate) {
                        $device->update($updateData);
                    }
                }
            }
        }

        return view('devices.index', compact('devices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('devices.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, GowaService $gowa)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'phone_number'    => 'required|string|max:20',
            'gowa_session_id' => 'required|string|unique:devices,gowa_session_id|alpha_dash',
            'webhook_url'     => 'nullable|url',
            'is_livechat_enabled' => 'required|boolean',
        ]);

        // 1. Tembak API GOWA untuk meregistrasikan device_id
        $gowaResponse = $gowa->createDevice($request->gowa_session_id);

        // 2. Validasi balikan dari GOWA
        if (!$gowaResponse || !isset($gowaResponse['code']) || $gowaResponse['code'] !== 'SUCCESS') {
            return back()->withInput()->with('error', 'Gagal mendaftarkan device ke server GOWA. Pastikan URL GOWA benar dan server menyala.');
        }

        // 3. Simpan ke database SIMAWA jika sukses di GOWA
        // Menggunakan state dari balikan GOWA ('disconnected') atau fallback ke 'pending'
        $initialState = $gowaResponse['results']['state'] ?? 'pending';
// 3. Simpan ke database jika sukses di GOWA
        auth()->user()->ownedDevices()->create([
            'name'            => $request->name,
            'phone_number'    => $request->phone_number,
            'gowa_session_id' => $request->gowa_session_id,
            'webhook_url'     => $request->webhook_url,
            'is_livechat_enabled' => $request->is_livechat_enabled,
            'status'          => 'pending', // <--- Paksa menjadi pending untuk device baru
        ]);

        return redirect()->route('devices.index')->with('success', 'Device berhasil ditambahkan ke GOWA! Silakan Scan QR Code untuk login.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
   /**
     * MENAMPILKAN HALAMAN EDIT DEVICE
     */
    public function edit(Device $device)
    {
        if (!auth()->user()->isSuperadmin() && $device->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit device ini.');
        }

        // Ambil semua list user untuk pilihan drop-down transfer device bagi Super Admin
        // Anda bisa memfilternya jika ada kolom role, misal: User::where('role', 'admin')->get()
        $admins = User::all();

        return view('devices.edit', compact('device', 'admins'));
    }

    /**
     * MENYIMPAN PERUBAHAN DATA DEVICE
     */
    public function update(Request $request, Device $device)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'gowa_session_id' => 'required|string|max:255',
            'webhook_url' => 'nullable|url|max:255',
            'is_livechat_enabled' => 'required|boolean',
            'api_key' => 'nullable|string|max:255|unique:devices,api_key,' . $device->id,
            'greeting_message' => 'nullable|string',
            'claim_message' => 'nullable|string',
            'closing_message' => 'nullable|string',
        ];

        // Validasi tambahan: Jika yang mengedit adalah Super Admin, wajib menyertakan user_id tujuan
        if (auth()->user()->isSuperadmin()) {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $request->validate($rules);

        $cleanNumber = preg_replace('/[^0-9]/', '', $request->phone_number);

        $updateData = [
            'name' => $request->name,
            'phone_number' => $cleanNumber,
            'gowa_session_id' => $request->gowa_session_id,
            'webhook_url' => $request->webhook_url,
            'is_livechat_enabled' => $request->is_livechat_enabled,
            'api_key' => $request->api_key,
            'greeting_message' => $request->greeting_message,
            'claim_message' => $request->claim_message,
            'closing_message' => $request->closing_message,
        ];
        //$updateData['api_key'] = $request->api_key;
        // Jalankan pemindahan kepemilikan jika diset oleh Super Admin
        if (auth()->user()->isSuperadmin()) {
            $updateData['user_id'] = $request->user_id;
        }

        $device->update($updateData);

        return redirect()->route('devices.index')->with('success', 'Data device dan penanggung jawab berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Device $device, GowaService $gowa)
    {
        $user = auth()->user();

        // Cek Hak Akses:
        // Jika BUKAN Superadmin DAN device ini bukan miliknya, maka tolak!
        if (!$user->isSuperadmin() && $device->user_id !== $user->id) {
            return back()->with('error', 'Unauthorized: Anda tidak berhak menghapus device ini.');
        }

        // 1. Coba hapus di server GOWA (agar session memory bersih)
        // Kita beri pengecekan if agar tidak error jika session_id kosong
        if ($device->gowa_session_id) {
            $gowa->deleteDevice($device->gowa_session_id);
        }

        // 2. Hapus dari database SIMAWA
        // Relasi di tabel pivot agent_device akan terhapus otomatis karena 'onDelete(cascade)'
        $device->delete();

        return redirect()->route('devices.index')->with('success', 'Device berhasil dihapus.');
    }
    // Fungsi untuk memunculkan halaman view saja
    public function scan(Device $device)
    {
        // Tambahkan pengecekan isSuperadmin() agar Super Admin bisa lewat
        if (!auth()->user()->isSuperadmin() && $device->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola device ini.');
        }
        return view('devices.scan', compact('device'));
    }

    public function requestQr(Device $device, \App\Services\GowaService $gowa)
    {
        if (!auth()->user()->isSuperadmin() && $device->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // 1. Cek status real di GOWA terlebih dahulu apakah device memang SUDAH login
        $statusCheck = $gowa->checkDeviceStatus($device->gowa_session_id);
        $alreadyLoggedIn = data_get($statusCheck, 'json_data.results.is_logged_in', false);

        if ($alreadyLoggedIn) {
            if ($device->status !== 'connected') {
                $device->update(['status' => 'connected']);
            }
            return response()->json([
                'status'  => 'already_logged_in',
                'message' => 'Device sudah terhubung ke WhatsApp.',
                'data'    => $statusCheck['json_data'] ?? null
            ]);
        }

        // Jika belum login, pastikan status di SIMAWA bukan 'connected'
        if ($device->status === 'connected') {
            $device->update(['status' => 'disconnected']);
        }

        // 2. Minta QR code baru ke GOWA v9
        $gowaResponse = $gowa->getLoginCode($device->gowa_session_id);

        // Jika GOWA melaporkan device already logged in
        $message = strtolower($gowaResponse['message'] ?? '');
        if (str_contains($message, 'already logged')) {
            $device->update(['status' => 'connected']);
            return response()->json([
                'status'  => 'already_logged_in',
                'message' => 'Device sudah terhubung ke WhatsApp.',
                'data'    => $gowaResponse
            ]);
        }

        // =========================================================================
        // TRIK ANTI MIXED-CONTENT: Ubah semua URL HTTP/HTTPS gambar menjadi Base64
        // Gunakan client GOWA dengan Basic Auth agar berhasil mengunduh QR dari server
        // =========================================================================
        if (is_array($gowaResponse)) {
            array_walk_recursive($gowaResponse, function(&$value, $key) use ($gowa) {
                if (is_string($value) && (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) && str_ends_with(strtolower($value), '.png')) {
                    try {
                        $imageResponse = $gowa->baseClient(10)->get($value);
                        if ($imageResponse->successful()) {
                            $value = 'data:image/png;base64,' . base64_encode($imageResponse->body());
                        }
                    } catch (\Exception $e) {
                        // Abaikan jika gagal, biarkan URL aslinya
                    }
                }
            });
        }

        return response()->json([
            'status' => 'success',
            'data'   => $gowaResponse
        ]);
    }

    // Fungsi untuk tombol Check Status di halaman tabel Device
    public function checkStatus(Device $device, GowaService $gowa)
    {
        $response = $gowa->checkDeviceStatus($device->gowa_session_id);

        if ($response && $response['http_status'] === 200 && isset($response['json_data']['code']) && $response['json_data']['code'] === 'SUCCESS') {
            $results = $response['json_data']['results'] ?? [];
            $state = strtolower($results['state'] ?? '');
            $isLoggedIn = $results['is_logged_in'] ?? ($state === 'logged_in');

            $newStatus = $isLoggedIn ? 'connected' : 'disconnected';
            $updateData = ['status' => $newStatus];

            $phone = $results['phone_number'] ?? null;
            if (!$phone && !empty($results['jid'])) {
                $phone = explode('@', explode(':', $results['jid'])[0])[0];
            }
            if ($phone && $device->phone_number !== $phone) {
                $updateData['phone_number'] = $phone;
            }

            // Jika terhubung, ambil dan perbarui profil WhatsApp (nama & avatar)
            if ($newStatus === 'connected') {
                Cache::forget("device_wa_profile_{$device->id}");
                $profile = $gowa->getConnectedWhatsAppProfile($device->gowa_session_id);
                if ($profile) {
                    if (!empty($profile['name'])) $updateData['whatsapp_name'] = $profile['name'];
                    if (!empty($profile['avatar'])) $updateData['whatsapp_avatar'] = $profile['avatar'];
                    if (!empty($profile['phone'])) $updateData['phone_number'] = $profile['phone'];
                }
            } else {
                Cache::forget("device_wa_profile_{$device->id}");
            }

            $device->update($updateData);

            if ($newStatus === 'connected') {
                $accountInfo = !empty($device->whatsapp_name) ? " (Akun: {$device->whatsapp_name})" : "";
                return back()->with('success', "Sinkronisasi berhasil: Device Anda SEDANG TERHUBUNG{$accountInfo}.");
            } else {
                return back()->with('error', 'Sinkronisasi selesai: Device Anda BELUM TERHUBUNG (Disconnected).');
            }
        }

        return back()->with('error', 'Gagal menghubungi server GOWA atau response tidak valid.');
    }

    public function reconnect(Device $device, GowaService $gowa)
    {
        try {
            $response = $gowa->reconnectDevice($device->gowa_session_id);
            if ($response && $response->successful()) {
                return back()->with('success', 'Perintah reconnect berhasil dikirim ke server GOWA untuk device ' . $device->name . '.');
            }

            $errMessage = $response ? ($response->json('message') ?? $response->body()) : 'Server GOWA tidak merespon';
            return back()->with('error', 'Gagal reconnect ke GOWA: ' . $errMessage);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat reconnect: ' . $e->getMessage());
        }
    }

    public function logout(Device $device, GowaService $gowa)
    {
        try {
            $response = $gowa->logoutDevice($device->gowa_session_id);
            Cache::forget("device_wa_profile_{$device->id}");
            $device->update([
                'status' => 'disconnected',
                'whatsapp_name' => null,
                'whatsapp_avatar' => null,
            ]);
            return back()->with('success', 'Sesi WhatsApp pada device ' . $device->name . ' berhasil diputus (Logout).');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat logout: ' . $e->getMessage());
        }
    }

    public function passkeyConfirm(Device $device, GowaService $gowa)
    {
        try {
            $response = $gowa->confirmPasskey($device->gowa_session_id);
            if ($response && $response->successful()) {
                return response()->json(['status' => 'success', 'message' => 'Konfirmasi passkey berhasil dikirim ke server GOWA.']);
            }
            $errMsg = $response ? ($response->json('message') ?? $response->body()) : 'Gagal konfirmasi passkey ke GOWA.';
            return response()->json(['status' => 'error', 'message' => $errMsg], 400);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function passkeyResponse(Device $device, Request $request, GowaService $gowa)
    {
        try {
            $response = $gowa->submitPasskeyResponse($device->gowa_session_id, $request->all());
            if ($response && $response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Passkey assertion berhasil dikirim.',
                    'data' => $response->json(),
                ]);
            }
            $errMsg = $response ? ($response->json('message') ?? $response->body()) : 'Gagal mengirim assertion ke GOWA';
            return response()->json(['status' => 'error', 'message' => $errMsg], 400);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function requestPairingCode(Device $device, Request $request, GowaService $gowa)
    {
        $request->validate([
            'phone_number' => 'required|string',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $request->phone_number);
        if (empty($phone)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nomor WhatsApp tidak valid.',
            ], 422);
        }

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        $res = $gowa->getPairingCode($device->gowa_session_id, $phone);

        if ($res && ((isset($res['code']) && $res['code'] === 'SUCCESS') || isset($res['results']['pair_code']))) {
            $pairCode = $res['results']['pair_code'] ?? null;
            if ($pairCode) {
                if ($device->phone_number !== $phone) {
                    $device->update(['phone_number' => $phone]);
                }
                return response()->json([
                    'status' => 'success',
                    'pair_code' => $pairCode,
                    'phone' => $phone,
                    'data' => $res,
                ]);
            }
        }

        $message = $res['message'] ?? 'Gagal membuat kode pairing WhatsApp dari server GOWA.';
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], 400);
    }

    public function statusJson(Device $device, GowaService $gowa)
    {
        try {
            if (!$device->gowa_session_id) {
                return response()->json(['status' => 'disconnected', 'phone_number' => $device->phone_number]);
            }

            $response = $gowa->getDeviceStatus($device->gowa_session_id);

            if ($response && $response->successful()) {
                $data = $response->json();
                $results = $data['results'] ?? $data;
                $apiState = strtolower($results['state'] ?? '');
                // Di GOWA: 'connected' = socket terhubung ke server WhatsApp, BELUM scan QR/login.
                // Device BARU dianggap logged in jika state == 'logged_in', is_logged_in == true, atau jid terisi.
                $isLoggedIn = ($apiState === 'logged_in') || (!empty($results['is_logged_in'])) || (!empty($results['jid']));

                if ($isLoggedIn) {
                    $status = 'connected';

                    $jid = $results['jid'] ?? null;
                    $phone = $results['phone_number'] ?? null;
                    if (!$phone && $jid) {
                        $phone = explode('@', explode(':', $jid)[0])[0];
                    }

                    $updateData = ['status' => $status];
                    if ($phone && $device->phone_number !== $phone) {
                        $updateData['phone_number'] = $phone;
                    }

                    if (empty($device->whatsapp_name) || empty($device->whatsapp_avatar)) {
                        $profile = $gowa->getConnectedWhatsAppProfile($device->gowa_session_id);
                        if ($profile) {
                            if (!empty($profile['name'])) $updateData['whatsapp_name'] = $profile['name'];
                            if (!empty($profile['avatar'])) $updateData['whatsapp_avatar'] = $profile['avatar'];
                            if (!empty($profile['phone'])) $updateData['phone_number'] = $profile['phone'];
                        }
                    }

                    $device->update($updateData);

                    return response()->json([
                        'status' => $status,
                        'phone_number' => $device->phone_number,
                        'whatsapp_name' => $device->whatsapp_name,
                        'whatsapp_avatar' => $device->whatsapp_avatar,
                    ]);
                } else {
                    if ($device->status !== 'disconnected') {
                        $device->update(['status' => 'disconnected']);
                    }

                    // Cek apakah WhatsApp mengirimkan tantangan Passkey (WebAuthn / Confirmation Code)
                    $passkeyInfo = null;
                    try {
                        $passkeyCheck = $gowa->getPasskeyStatus($device->gowa_session_id);
                        if ($passkeyCheck && isset($passkeyCheck['results'])) {
                            $pkResults = $passkeyCheck['results'];
                            $pkStatus = $pkResults['status'] ?? 'none';
                            if ($pkStatus !== 'none') {
                                $passkeyInfo = [
                                    'status'          => $pkStatus,
                                    'code'            => $pkResults['code'] ?? null,
                                    'challenge'       => $pkResults['challenge'] ?? null,
                                    'skip_handoff_ux' => $pkResults['skip_handoff_ux'] ?? false,
                                    'device_id'       => $pkResults['device_id'] ?? $device->gowa_session_id,
                                ];
                            }
                        }
                    } catch (\Exception $e) {
                        // Abaikan jika endpoint passkey gagal
                    }

                    return response()->json([
                        'status' => 'disconnected',
                        'phone_number' => $device->phone_number,
                        'passkey' => $passkeyInfo,
                    ]);
                }
            } else {
                $errorData = $response ? $response->json() : [];
                if (isset($errorData['message']) && str_contains(strtolower($errorData['message']), 'not found')) {
                    if ($device->status !== 'disconnected') {
                        $device->update(['status' => 'disconnected']);
                    }
                    \Log::warning("Session GOWA [{$device->gowa_session_id}] tidak ditemukan di GOWA.");
                } else if ($response) {
                    \Log::error('GOWA HTTP Error: ' . $response->status() . ' - ' . $response->body());
                }
            }
        } catch (\Exception $e) {
            \Log::error('Koneksi GOWA Error: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'disconnected',
            'phone_number' => $device->phone_number ?? '-'
        ]);
    }

    /**
     * SINKRONISASI DAFTAR DEVICE DARI SERVER GOWA (v9)
     */
    public function syncDevices(GowaService $gowa)
    {
        if (!auth()->user()->isSuperadmin()) {
            abort(403, 'Hanya Super Admin yang diizinkan melakukan sinkronisasi device.');
        }

        try {
            $data = $gowa->getDevices();

            if ($data && isset($data['code']) && $data['code'] === 'SUCCESS') {
                $results = $data['results'] ?? [];
                $inserted = 0;
                $updated = 0;

                foreach ($results as $res) {
                    $gowaId = $res['id'] ?? $res['device'] ?? null;
                    if (!$gowaId) continue;

                    $gowaState = strtolower($res['state'] ?? 'disconnected');
                    $isLoggedIn = $res['is_logged_in'] ?? ($gowaState === 'logged_in');
                    $simawaStatus = $isLoggedIn ? 'connected' : 'disconnected';

                    $phoneNumber = $res['phone_number'] ?? null;
                    if (empty($phoneNumber) && !empty($res['jid'])) {
                        $phoneNumber = explode('@', explode(':', $res['jid'])[0])[0];
                    }

                    $deviceName = $res['display_name'] ?? $res['name'] ?? $gowaId;

                    $device = Device::where('gowa_session_id', $gowaId)->first();

                    if ($device) {
                        $updateData = ['status' => $simawaStatus];
                        if (!empty($phoneNumber)) {
                            $updateData['phone_number'] = $phoneNumber;
                        }
                        if ($simawaStatus === 'connected') {
                            $profile = $gowa->getConnectedWhatsAppProfile($gowaId);
                            if ($profile) {
                                if (!empty($profile['name'])) $updateData['whatsapp_name'] = $profile['name'];
                                if (!empty($profile['avatar'])) $updateData['whatsapp_avatar'] = $profile['avatar'];
                                if (!empty($profile['phone'])) $updateData['phone_number'] = $profile['phone'];
                            }
                        }
                        $device->update($updateData);
                        $updated++;
                    } else {
                        $profile = $simawaStatus === 'connected' ? $gowa->getConnectedWhatsAppProfile($gowaId) : null;
                        Device::create([
                            'name' => $deviceName,
                            'phone_number' => $profile['phone'] ?? ($phoneNumber ?? ''),
                            'whatsapp_name' => $profile['name'] ?? null,
                            'whatsapp_avatar' => $profile['avatar'] ?? null,
                            'gowa_session_id' => $gowaId,
                            'status' => $simawaStatus,
                            'webhook_url' => null,
                            'is_livechat_enabled' => true,
                            'user_id' => auth()->id(),
                        ]);
                        $inserted++;
                    }
                }

                return back()->with('success', "Sinkronisasi Berhasil! Berhasil menambah {$inserted} device baru dan memperbarui {$updated} status device.");
            }

            return back()->with('error', 'Gagal terhubung dengan server GOWA atau response tidak valid.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan sistem saat sinkronisasi: ' . $e->getMessage());
        }
    }
}
