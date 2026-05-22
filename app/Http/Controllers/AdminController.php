<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminController extends Controller
{
    public function index()
    {
        // Ambil semua user admin, lalu hitung jumlah agen dan devicenya secara otomatis
        $admins = User::where('role', 'admin')
                    ->withCount([
                        'subordinates as agents_count',
                        'devices as devices_count' // Menghitung relasi devices()
                    ])
                    ->latest()
                    ->paginate(10);

        return view('admins.index', compact('admins'));
    }

    public function create()
    {
        return view('admins.create');
    }

    public function store(Request $request)
        {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users'], // Tambahkan validasi username
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'confirmed', 'min:4'], // Ubah menjadi minimal 4 karakter
            ]);

            User::create([
                'name' => $request->name,
                'username' => $request->username, // Masukkan username ke database
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'admin',
                'parent_id' => null,
            ]);

            return redirect()->route('admins.index')->with('success', 'Akun Admin berhasil ditambahkan.');
        }

    public function destroy(User $admin)
    {
        // Pastikan yang dihapus benar-benar admin
        if ($admin->role === 'admin') {
            $admin->delete(); // Karena on-delete cascade, agen dan device miliknya otomatis terhapus
            return redirect()->route('admins.index')->with('success', 'Admin beserta seluruh Agen dan Device miliknya berhasil dihapus.');
        }

        return redirect()->route('admins.index')->with('error', 'Tindakan tidak diizinkan.');
    }
    public function edit(User $admin)
    {
        // Pastikan yang diedit benar-benar role admin
        if ($admin->role !== 'admin') {
            return redirect()->route('admins.index')->with('error', 'Tindakan tidak diizinkan.');
        }

        return view('admins.edit', compact('admin'));
    }

    public function update(Request $request, User $admin)
    {
        if ($admin->role !== 'admin') {
            return redirect()->route('admins.index')->with('error', 'Tindakan tidak diizinkan.');
        }

        $request->validate([
            'password' => ['required', 'confirmed', 'min:4'],
        ]);

        $admin->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admins.index')->with('success', 'Password untuk Admin ' . $admin->name . ' berhasil diubah.');
    }
}
