<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Device;
use App\Services\ExcelSpreadsheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AgentController extends Controller
{
    /**
     * Pastikan hanya Admin atau Superadmin yang dapat mengakses fitur manajemen agen.
     */
    private function ensureAdminOrSuperadmin(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->isSuperadmin() && !$user->isAdmin())) {
            abort(403, 'Hanya Admin dan Superadmin yang dapat menggunakan fitur ini.');
        }
    }

    public function index(Request $request)
    {
        $this->ensureAdminOrSuperadmin();
        $user = auth()->user();

        $query = User::where('role', 'agent')->with(['handledDevices', 'supervisor']);

        // Jika yang login adalah Admin, hanya tampilkan agen miliknya
        if ($user->isAdmin()) {
            $query->where('parent_id', $user->id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $agents = $query->latest()->paginate(10)->withQueryString();

        $deviceQuery = Device::query()->orderBy('name');
        if ($user->isAdmin()) {
            $deviceQuery->where('user_id', $user->id);
        }
        $devices = $deviceQuery->get();

        return view('agents.index', compact('agents', 'devices'));
    }

    public function create()
    {
        $this->ensureAdminOrSuperadmin();
        $user = auth()->user();

        $deviceQuery = Device::query();

        // Jika yang login Admin, hanya tampilkan device miliknya untuk dipilih
        if ($user->isAdmin()) {
            $deviceQuery->where('user_id', $user->id);
        }

        $devices = $deviceQuery->get();
        return view('agents.create', compact('devices'));
    }

    public function store(Request $request)
    {
        $this->ensureAdminOrSuperadmin();
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:4'],
            'devices' => ['nullable', 'array'],
            'devices.*' => ['exists:devices,id'],
        ]);

        $agent = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'agent',
            'parent_id' => auth()->id(), // Tag ke Admin pembuatnya
        ]);

        if ($request->has('devices')) {
            $devicesToSync = $request->input('devices', []);
            if (auth()->user()->isAdmin()) {
                $allowedIds = Device::where('user_id', auth()->id())->pluck('id')->toArray();
                $devicesToSync = array_values(array_intersect($devicesToSync, $allowedIds));
            }
            $agent->handledDevices()->sync($devicesToSync);
        }

        return redirect()->route('agents.index')->with('success', 'Agen berhasil ditambahkan.');
    }

    public function edit(User $agent)
    {
        $this->ensureAdminOrSuperadmin();
        // Keamanan: Pastikan Admin tidak mengedit agen milik orang lain
        if (auth()->user()->isAdmin() && $agent->parent_id !== auth()->id()) {
            return redirect()->route('agents.index')->with('error', 'Akses ditolak.');
        }

        $user = auth()->user();
        $deviceQuery = Device::query();

        if ($user->isAdmin()) {
            $deviceQuery->where('user_id', $user->id);
        } elseif (!$user->isSuperadmin() && $agent->parent_id) {
            $deviceQuery->where('user_id', $agent->parent_id);
        }

        $devices = $deviceQuery->get();
        $assignedDeviceIds = $agent->handledDevices()->pluck('devices.id')->toArray();

        return view('agents.edit', compact('agent', 'devices', 'assignedDeviceIds'));
    }

    public function update(Request $request, User $agent)
    {
        $this->ensureAdminOrSuperadmin();
        // Keamanan: Pastikan Admin tidak mengedit agen milik orang lain
        if (auth()->user()->isAdmin() && $agent->parent_id !== auth()->id()) {
            return redirect()->route('agents.index')->with('error', 'Akses ditolak.');
        }

        // ==========================================
        // JIKA YANG DI-SUBMIT ADALAH FORM PASSWORD
        // ==========================================
        if ($request->form_type === 'password') {
            $request->validate([
                'password' => ['required', 'confirmed', 'min:4'],
            ]);

            $agent->update([
                'password' => Hash::make($request->password),
            ]);

            return back()->with('success', 'Password Agen ' . $agent->name . ' berhasil direset.');
        }

        // ==========================================
        // JIKA YANG DI-SUBMIT ADALAH FORM INFO PROFIL & AKSES DEVICE
        // ==========================================
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // Pengecekan unique mengabaikan ID agen ini sendiri agar tidak error saat klik save tanpa mengubah username
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $agent->id],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $agent->id],
            'devices' => ['nullable', 'array'],
            'devices.*' => ['exists:devices,id'],
        ]);

        $agent->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
        ]);

        $devicesToSync = $request->input('devices', []);
        if (auth()->user()->isAdmin()) {
            $allowedIds = Device::where('user_id', auth()->id())->pluck('id')->toArray();
            $devicesToSync = array_values(array_intersect($devicesToSync, $allowedIds));
        }

        $agent->handledDevices()->sync($devicesToSync);

        return redirect()->route('agents.index')->with('success', 'Informasi Profil dan Akses Device Agen ' . $agent->name . ' berhasil diperbarui.');
    }

    public function destroy(User $agent)
    {
        $this->ensureAdminOrSuperadmin();
        if (auth()->user()->isAdmin() && $agent->parent_id !== auth()->id()) {
            return redirect()->route('agents.index')->with('error', 'Akses ditolak.');
        }

        $agent->delete();
        return redirect()->route('agents.index')->with('success', 'Agen berhasil dihapus.');
    }

    /**
     * MENGUNDUH TEMPLATE EXCEL (.XLS) UNTUK IMPORT AGEN
     */
    public function downloadTemplate(ExcelSpreadsheetService $excel)
    {
        $this->ensureAdminOrSuperadmin();

        $rows = [
            ['Nama Agen', 'Username', 'Email', 'Password'],
            ['Agen CS 1', 'agencs1', 'cs1@simawa.com', 'rahasia123'],
            ['Agen CS 2', 'agencs2', 'cs2@simawa.com', 'rahasia123'],
        ];

        $binary = $excel->generateBiff8Xls($rows, 'Template Agen');

        return response($binary, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="template_import_agen.xls"',
            'Pragma' => 'public',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    /**
     * FUNGSI IMPORT FILE EXCEL (.XLS / .XLSX / .CSV) KE TABEL USERS (ROLE: AGENT)
     * - Jika sudah ada device yang terdaftar, akses device otomatis ditambahkan ke agen.
     * - Jika belum ada device, tidak perlu menambahkan device.
     */
    public function import(Request $request, ExcelSpreadsheetService $excel)
    {
        $this->ensureAdminOrSuperadmin();

        $request->validate([
            'file' => [
                'required',
                'file',
                'max:5120',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['xls', 'xlsx', 'csv'], true)) {
                        $fail('File harus berformat .xls, .xlsx, atau .csv.');
                    }
                },
            ],
            'devices' => ['nullable', 'array'],
            'devices.*' => ['integer', 'exists:devices,id'],
        ]);

        $file = $request->file('file');
        try {
            $rows = $excel->parseRows($file->getRealPath(), $file->getClientOriginalExtension());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file Excel: ' . $e->getMessage());
        }

        if (empty($rows)) {
            return back()->with('error', 'File Excel kosong atau tidak memiliki baris data.');
        }

        // Lewati baris header judul kolom jika baris pertama berupa header
        $firstRowText = strtolower(implode(' ', $rows[0] ?? []));
        if (str_contains($firstRowText, 'nama') || str_contains($firstRowText, 'username') || str_contains($firstRowText, 'email')) {
            array_shift($rows);
        }

        if (empty($rows)) {
            return back()->with('error', 'Tidak ditemukan baris data agen di dalam file Excel.');
        }

        $user = auth()->user();
        $allowedDevicesQuery = Device::query()->orderBy('name');
        if ($user->isAdmin()) {
            $allowedDevicesQuery->where('user_id', $user->id);
        }
        $allowedDevices = $allowedDevicesQuery->get();
        $allowedDeviceIds = $allowedDevices->pluck('id')->toArray();

        // Tentukan device yang akan ditambahkan otomatis jika sudah ada device
        $defaultDeviceIds = [];
        if (!empty($allowedDeviceIds)) {
            if ($request->boolean('devices_submitted')) {
                $requested = array_map('intval', (array) $request->input('devices', []));
                $defaultDeviceIds = array_values(array_intersect($requested, $allowedDeviceIds));
            } else {
                $defaultDeviceIds = $allowedDeviceIds;
            }
        }

        $importedCount = 0;
        $attachedDeviceCount = 0;
        $skippedReasons = [];

        foreach ($rows as $rowIndex => $row) {
            $name = trim((string) ($row[0] ?? ''));
            $username = trim((string) ($row[1] ?? ''));
            $email = trim((string) ($row[2] ?? ''));
            $rawPassword = trim((string) ($row[3] ?? ''));
            $rawDeviceCell = trim((string) ($row[4] ?? ''));

            if ($name === '' || $username === '' || $email === '') {
                continue;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skippedReasons[] = "Email tidak valid ({$email})";
                continue;
            }

            $existingByEmail = User::where('email', $email)->first();
            if ($existingByEmail) {
                if (!$existingByEmail->isAgent() || ($user->isAdmin() && $existingByEmail->parent_id !== $user->id)) {
                    $skippedReasons[] = "Email sudah terdaftar sebagai akun lain ({$email})";
                    continue;
                }
                if (User::where('username', $username)->where('id', '!=', $existingByEmail->id)->exists()) {
                    $skippedReasons[] = "Username sudah dipakai ({$username})";
                    continue;
                }

                $updateData = [
                    'name' => $name,
                    'username' => $username,
                ];
                if ($rawPassword !== '') {
                    $updateData['password'] = Hash::make($rawPassword);
                }
                $existingByEmail->update($updateData);
                $agent = $existingByEmail;
            } else {
                if (User::where('username', $username)->exists()) {
                    $skippedReasons[] = "Username sudah dipakai ({$username})";
                    continue;
                }

                $agent = User::create([
                    'name' => $name,
                    'username' => $username,
                    'email' => $email,
                    'password' => Hash::make($rawPassword !== '' ? $rawPassword : '123456'),
                    'role' => 'agent',
                    'is_active' => true,
                    'parent_id' => $user->id,
                ]);
            }

            // Jika sudah ada device yang terdaftar, langsung tambahkan akses device ke agen
            if (!empty($allowedDeviceIds)) {
                $rowDeviceIds = $defaultDeviceIds;

                // Opsional: jika kolom ke-5 di Excel menyebut nama/ID/nomor device spesifik
                if ($rawDeviceCell !== '') {
                    $tokens = array_filter(array_map('trim', explode(',', $rawDeviceCell)));
                    $matched = [];
                    foreach ($allowedDevices as $dev) {
                        foreach ($tokens as $tok) {
                            if (
                                strcasecmp($dev->name, $tok) === 0 ||
                                ((string) $dev->id === $tok) ||
                                (!empty($dev->phone_number) && preg_replace('/[^0-9]/', '', $dev->phone_number) === preg_replace('/[^0-9]/', '', $tok))
                            ) {
                                $matched[] = $dev->id;
                            }
                        }
                    }
                    $rowDeviceIds = array_values(array_unique($matched));
                }

                if (!empty($rowDeviceIds)) {
                    $agent->handledDevices()->syncWithoutDetaching($rowDeviceIds);
                    $attachedDeviceCount++;
                }
            }

            $importedCount++;
        }

        if ($importedCount === 0 && !empty($skippedReasons)) {
            return back()->with('error', 'Tidak ada agen yang berhasil diimpor. Detail: ' . implode('; ', array_slice($skippedReasons, 0, 5)));
        }

        $message = "Berhasil mengimpor {$importedCount} agen ke dalam sistem.";
        if ($attachedDeviceCount > 0) {
            $message .= " Akses device otomatis ditambahkan ke {$attachedDeviceCount} agen.";
        } elseif (empty($allowedDeviceIds)) {
            $message .= " Belum ada device terdaftar, sehingga akses device tidak ditambahkan.";
        }
        if (!empty($skippedReasons)) {
            $message .= " Dilewati (" . count($skippedReasons) . "): " . implode('; ', array_slice($skippedReasons, 0, 3));
        }

        return back()->with('success', $message);
    }

    /**
     * FUNGSI HAPUS MASSAL (BULK DELETE)
     */
    public function bulkDelete(Request $request)
    {
        $this->ensureAdminOrSuperadmin();
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id'
        ]);

        $query = User::whereIn('id', $request->ids)->where('role', 'agent');

        // Proteksi: Jika yang menghapus adalah Admin, pastikan hanya agen miliknya yang terhapus
        if (auth()->user()->isAdmin()) {
            $query->where('parent_id', auth()->id());
        }

        $deletedCount = $query->delete();

        return back()->with('success', "$deletedCount agen berhasil dihapus sekaligus.");
    }
}
