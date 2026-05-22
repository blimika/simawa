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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
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
        if ($device->user_id !== auth()->id()) {
            abort(403);
        }
        return view('devices.scan', compact('device'));
    }

    public function requestQr(Device $device, GowaService $gowa)
    {
        if ($device->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Panggil service GOWA
        $gowaResponse = $gowa->getLoginCode($device->gowa_session_id);

        // Kirimkan balikan ke frontend dengan key 'data'
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
}
