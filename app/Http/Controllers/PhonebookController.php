<?php

namespace App\Http\Controllers;

use App\Models\Phonebook;
use Illuminate\Http\Request;

class PhonebookController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $query = Phonebook::query();

        // Jika bukan Superadmin, filter agar hanya melihat kontak milik Admin-nya saja
        if (!$user->isSuperadmin()) {
            $query->where('user_id', $user->getOwnerId());
        }

        // Jika Superadmin, kita tampilkan juga relasi owner untuk melihat ini kontak milik siapa
        $contacts = $query->with('owner')->latest()->paginate(15);

        return view('phonebook.index', compact('contacts'));
    }

    public function create()
    {
        // Mengambil daftar kategori unik yang sudah pernah dibuat oleh Admin ini
        $categories = Phonebook::where('user_id', auth()->user()->getOwnerId())
                        ->whereNotNull('category')
                        ->distinct()
                        ->pluck('category');

        return view('phonebook.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:20',
            'category' => 'nullable|string|max:100', // <-- Validasi untuk kategori
        ]);

        $number = preg_replace('/[^0-9]/', '', $request->number);
        if (substr($number, 0, 1) == '0') {
            $number = '62' . substr($number, 1);
        }

        Phonebook::create([
            'user_id' => auth()->user()->getOwnerId(),
            'name' => $request->name,
            'number' => $number,
            'category' => $request->category, // <-- Simpan kategorinya
        ]);

        return redirect()->route('phonebook.index')->with('success', 'Kontak berhasil disimpan.');
    }

    public function destroy(Phonebook $phonebook)
    {
        $user = auth()->user();

        // Proteksi: Pastikan yang menghapus adalah pemiliknya atau Superadmin
        if (!$user->isSuperadmin() && $phonebook->user_id !== $user->getOwnerId()) {
            return redirect()->route('phonebook.index')->with('error', 'Akses ditolak.');
        }

        $phonebook->delete();
        return redirect()->route('phonebook.index')->with('success', 'Kontak berhasil dihapus.');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:255',
            'category' => 'nullable|string|max:255', // Tambahkan validasi untuk kategori
        ]);

        $phonebook = \App\Models\Phonebook::findOrFail($id);

        $cleanNumber = preg_replace('/[^0-9]/', '', $request->number);

        $phonebook->update([
            'name' => $request->name,
            'number' => $cleanNumber,
            'category' => $request->category, // Simpan inputan kategori
        ]);

        return back()->with('success', 'Data kontak berhasil diperbarui!');
    }
}
