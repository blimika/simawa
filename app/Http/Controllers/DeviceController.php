<?php

namespace App\Http\Controllers;
use App\Models\Device;
use Illuminate\Http\Request;
use App\Services\GowaService; // Import Service yang baru dibuat
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
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

        // Panggil service GOWA
        $gowaResponse = $gowa->getLoginCode($device->gowa_session_id);

        // =========================================================================
        // TRIK ANTI MIXED-CONTENT: Ubah semua URL HTTP gambar menjadi Base64
        // =========================================================================
        if (is_array($gowaResponse)) {
            array_walk_recursive($gowaResponse, function(&$value, $key) {
                // Jika value berupa teks, berawalan http://, dan berakhiran .png
                if (is_string($value) && str_starts_with($value, 'http://') && str_ends_with(strtolower($value), '.png')) {
                    try {
                        // Download gambar secara diam-diam via backend Laravel
                        $imageResponse = \Illuminate\Support\Facades\Http::timeout(10)->get($value);
                        
                        if ($imageResponse->successful()) {
                            // Timpa URL dengan teks Base64 (Format Data URI)
                            $value = 'data:image/png;base64,' . base64_encode($imageResponse->body());
                        }
                    } catch (\Exception $e) {
                        // Abaikan jika gagal, biarkan URL aslinya
                    }
                }
            });
        }

        // Kirimkan balikan ke frontend
        return response()->json([
            'status' => 'success',
            'data'   => $gowaResponse
        ]);
    }
    // Fungsi untuk tombol Check Status di halaman tabel Device
    public function checkStatus(Device $device, GowaService $gowa)
    {
        // Panggil endpoint /app/status GOWA
        $response = $gowa->checkDeviceStatus($device->gowa_session_id);

        if ($response && $response['http_status'] === 200 && isset($response['json_data']['code']) && $response['json_data']['code'] === 'SUCCESS') {

            // Ambil nilai is_logged_in (true / false)
            $isLoggedIn = $response['json_data']['results']['is_logged_in'] ?? false;

            $newStatus = $isLoggedIn ? 'connected' : 'disconnected';

            // Update database SIMAWA jika ada perubahan
            if ($device->status !== $newStatus) {
                $device->update(['status' => $newStatus]);
            }

            if ($newStatus === 'connected') {
                return back()->with('success', 'Sinkronisasi berhasil: Device Anda SEDANG TERHUBUNG (Logged In).');
            } else {
                return back()->with('error', 'Sinkronisasi selesai: Device Anda BELUM TERHUBUNG (Disconnected).');
            }
        }

        return back()->with('error', 'Gagal menghubungi server GOWA atau response tidak valid.');
    }

    public function reconnect(Device $device, GowaService $gowa)
    {
        // Logika untuk menembak endpoint reconnect GOWA (misal POST /app/reconnect)

        return back()->with('success', 'Perintah reconnect dikirim ke device ' . $device->name . '.');
    }
    public function statusJson(Device $device, GowaService $gowa)
    {
        try {
            if (!$device->gowa_session_id) {
                return response()->json(['status' => 'disconnected', 'phone_number' => $device->phone_number]);
            }

            $response = $gowa->getDeviceStatus($device->gowa_session_id);

            if ($response->successful()) {
                $data = $response->json();
                $apiState = strtolower($data['results']['state'] ?? $data['state'] ?? 'disconnected');

                if (in_array($apiState, ['logged_in'])) {
                    $status = 'connected';

                    $jid = $data['results']['jid'] ?? $data['jid'] ?? null;
                    if ($jid) {
                        $cleanNumber = explode('@', explode(':', $jid)[0])[0];
                        if ($device->phone_number !== $cleanNumber) {
                            $device->update(['phone_number' => $cleanNumber]);
                        }
                    }

                    if ($device->status !== $status) {
                        $device->update(['status' => $status]);
                    }

                    return response()->json(['status' => $status, 'phone_number' => $device->phone_number]);
                }
            } else {
                // ======== BAGIAN PERBAIKAN UNTUK "NOT FOUND" ========
                $errorData = $response->json();

                // Jika GOWA membalas "device not found", paksa status ke disconnected di DB
                if (isset($errorData['message']) && str_contains(strtolower($errorData['message']), 'not found')) {
                    if ($device->status !== 'disconnected') {
                        $device->update(['status' => 'disconnected']);
                    }
                    \Log::warning("Session GOWA [{$device->gowa_session_id}] hilang. User perlu scan ulang.");
                } else {
                    \Log::error('GOWA HTTP Error: ' . $response->status() . ' - ' . $response->body());
                }
            }

        } catch (\Exception $e) {
        \Log::error('Koneksi GOWA Error: ' . $e->getMessage());
        }

        // Pastikan fallback ini selalu mengembalikan kunci 'status'
        return response()->json([
            'status' => 'disconnected', // JANGAN sampai null atau undefined
            'phone_number' => $device->phone_number ?? '-'
        ]);
    }
    /**
     * SINKRONISASI DAFTAR DEVICE DARI SERVER GOWA
     */
    public function syncDevices()
    {
        // 1. Proteksi Keamanan: Pastikan hanya Super Admin yang bisa mengakses fungsi ini
        if (!auth()->user()->isSuperadmin()) {
            abort(403, 'Hanya Super Admin yang diizinkan melakukan sinkronisasi device.');
        }

        // 2. Ambil Kredensial API GOWA dari file .env
        $gowaBaseUrl = env('GOWA_API_URL', 'http://localhost:5271');
        $gowaUser = env('GOWA_BASIC_USER', 'kota');
        $gowaPass = env('GOWA_BASIC_PASS', 'kota');

        try {
            // 3. Request data list device ke GOWA
            $response = \Illuminate\Support\Facades\Http::withBasicAuth($gowaUser, $gowaPass)
                ->timeout(10)
                ->get(rtrim($gowaBaseUrl, '/') . '/devices');

            if ($response->successful()) {
                $results = $response->json('results') ?? [];
                $inserted = 0;
                $updated = 0;

                // 4. Looping data hasil dari GOWA
                foreach ($results as $res) {
                    $gowaId = $res['id'];
                    $gowaState = $res['state'];

                    // Konversi Status: 'logged_in' dari GOWA diubah menjadi 'connected' agar sesuai dengan SIMAWA
                    $simawaStatus = ($gowaState === 'logged_in') ? 'connected' : 'disconnected';

                    // Ekstrak nomor HP dari JID (misal: 6281803635400@s.whatsapp.net menjadi 6281803635400)
                    $phoneNumber = null;
                    if (!empty($res['jid'])) {
                        $phoneNumber = explode('@', $res['jid'])[0];
                    }

                    // Tentukan Nama: Ambil display_name, jika null gunakan ID session sebagai nama default
                    $deviceName = $res['display_name'] ?? $gowaId;

                    // 5. Cek apakah device sudah terdaftar di SIMAWA
                    $device = \App\Models\Device::where('gowa_session_id', $gowaId)->first();

                    if ($device) {
                        // KONDISI A: Device SUDAH ADA -> Cukup update status dan nomor HP jika terdeteksi logged_in
                        $updateData = ['status' => $simawaStatus];
                        if (!empty($phoneNumber)) {
                            $updateData['phone_number'] = $phoneNumber;
                        }

                        $device->update($updateData);
                        $updated++;
                    } else {
                        // KONDISI B: Device BELUM ADA -> Otomatis buat data device baru di SIMAWA
                        \App\Models\Device::create([
                            'name' => $deviceName,
                            'phone_number' => $phoneNumber ?? '',
                            'gowa_session_id' => $gowaId,
                            'status' => $simawaStatus,
                            'webhook_url' => null,
                            'is_livechat_enabled' => true, // default diaktifkan livechatnya
                            'user_id' => auth()->id(), // Otomatis dikunci kepemilikannya ke Superadmin yang melakukan sync
                        ]);
                        $inserted++;
                    }
                }

                return back()->with('success', "Sinkronisasi Berhasil! Berhasil menambah {$inserted} device baru dan memperbarui {$updated} status device.");
            }

            return back()->with('error', 'Gagal terhubung dengan server GOWA. Kode Status: ' . $response->status());

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan sistem saat sinkronisasi: ' . $e->getMessage());
        }
    }
}
