<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AgentController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = User::where('role', 'agent')->with('handledDevices');

        // Jika yang login adalah Admin, hanya tampilkan agen miliknya
        if ($user->isAdmin()) {
            $query->where('parent_id', $user->id);
        }

        $agents = $query->latest()->paginate(10);
        return view('agents.index', compact('agents'));
    }

    public function create()
    {
        $user = auth()->user();

        $deviceQuery = Device::where('status', 'connected');

        // Jika yang login Admin, hanya tampilkan device miliknya untuk dipilih
        if ($user->isAdmin()) {
            $deviceQuery->where('user_id', $user->id);
        }

        $devices = $deviceQuery->get();
        return view('agents.create', compact('devices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', 'min:4'],
            'devices' => ['array'],
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
            $agent->handledDevices()->sync($request->devices);
        }

        return redirect()->route('agents.index')->with('success', 'Agen berhasil ditambahkan.');
    }

    public function edit(User $agent)
    {
        // Keamanan: Pastikan Admin tidak mengedit agen milik orang lain
        if (auth()->user()->isAdmin() && $agent->parent_id !== auth()->id()) {
            return redirect()->route('agents.index')->with('error', 'Akses ditolak.');
        }

        return view('agents.edit', compact('agent'));
    }

    public function update(Request $request, User $agent)
    {
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
        // JIKA YANG DI-SUBMIT ADALAH FORM INFO PROFIL
        // ==========================================
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // Pengecekan unique mengabaikan ID agen ini sendiri agar tidak error saat klik save tanpa mengubah username
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $agent->id],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $agent->id],
        ]);

        $agent->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
        ]);

        return redirect()->route('agents.index')->with('success', 'Informasi Profil Agen ' . $agent->name . ' berhasil diperbarui.');
    }

    public function destroy(User $agent)
    {
        if (auth()->user()->isAdmin() && $agent->parent_id !== auth()->id()) {
            return redirect()->route('agents.index')->with('error', 'Akses ditolak.');
        }

        $agent->delete();
        return redirect()->route('agents.index')->with('success', 'Agen berhasil dihapus.');
    }
    /**
     * MENGUNDUH TEMPLATE CSV UNTUK IMPORT AGEN
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=template_import_agen.csv',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        // Header Kolom
        $columns = ['Nama Agen', 'Username', 'Email', 'Password'];

        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            // Data Contoh
            fputcsv($file, ['Agen CS 1', 'agencs1', 'cs1@simawa.com', 'rahasia123']);
            fputcsv($file, ['Agen CS 2', 'agencs2', 'cs2@simawa.com', 'rahasia123']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * FUNGSI IMPORT CSV KE TABEL USERS (ROLE: AGENT)
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $fileHandle = fopen($file->getRealPath(), 'r');

        $header = fgetcsv($fileHandle); // Lewati baris judul

        $importedCount = 0;
        $parentId = auth()->id(); // Tag kepemilikan agen ke Admin yang sedang login

        while (($row = fgetcsv($fileHandle)) !== false) {
            $name = $row[0] ?? null;
            $username = $row[1] ?? null;
            $email = $row[2] ?? null;
            // Jika password di Excel kosong, gunakan default '123456'
            $password = !empty($row[3]) ? \Illuminate\Support\Facades\Hash::make($row[3]) : \Illuminate\Support\Facades\Hash::make('123456');

            // Lewati jika data inti kosong
            if (empty($name) || empty($username) || empty($email)) continue;

            // Simpan atau Update agen berdasarkan email
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'username' => $username,
                    'password' => $password,
                    'role' => 'agent',
                    'parent_id' => $parentId
                ]
            );

            $importedCount++;
        }

        fclose($fileHandle);

        return back()->with('success', "Berhasil mengimpor $importedCount agen ke dalam sistem.");
    }

    /**
     * FUNGSI HAPUS MASSAL (BULK DELETE)
     */
    public function bulkDelete(Request $request)
    {
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
