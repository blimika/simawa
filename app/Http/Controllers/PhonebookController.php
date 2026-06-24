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
    /**
     * MEMPERBARUI DATA KONTAK
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
        ]);

        $phonebook = Phonebook::findOrFail($id);

        // 1. Bersihkan karakter non-angka
        $cleanNumber = preg_replace('/[^0-9]/', '', $request->number);

        // 2. Auto-format dari 0 ke 62 agar konsisten dengan fungsi store & import
        if (str_starts_with($cleanNumber, '0')) {
            $cleanNumber = '62' . substr($cleanNumber, 1);
        }

        // 3. Jalankan pembaruan data
        $phonebook->update([
            'name' => $request->name,
            'number' => $cleanNumber,
            'category' => $request->category,
        ]);

        // 4. DIUBAH DI SINI: Alihkan ke halaman index dengan membawa notifikasi sukses
        return redirect()->route('phonebook.index')->with('success', 'Data kontak berhasil diperbarui!');
    }
    /**
     * FUNGSI IMPORT CSV EXCEL KE PHONEBOOK
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $fileHandle = fopen($file->getRealPath(), 'r');

        // Lewati baris pertama (Header)
        $header = fgetcsv($fileHandle);

        $importedCount = 0;
        $ownerId = auth()->user()->getOwnerId();

        while (($row = fgetcsv($fileHandle)) !== false) {
            $name = $row[0] ?? 'Tanpa Nama';
            $number = $row[1] ?? '';
            // AMBIL DATA KATEGORI DARI KOLOM KE-3 (INDEX 2)
            $category = !empty($row[2]) ? trim($row[2]) : null;

            if (empty($number)) continue;

            $cleanNumber = preg_replace('/[^0-9]/', '', $number);
            if (str_starts_with($cleanNumber, '0')) {
                $cleanNumber = '62' . substr($cleanNumber, 1);
            }

            // Simpan data beserta kategorinya
            \App\Models\Phonebook::updateOrCreate(
                [
                    'number' => $cleanNumber,
                    'user_id' => $ownerId
                ],
                [
                    'name' => $name,
                    'category' => $category // Menggunakan kategori dari file Excel
                ]
            );

            $importedCount++;
        }

        fclose($fileHandle);

        return back()->with('success', "Berhasil mengimpor $importedCount kontak ke Buku Telepon.");
    }

    /**
     * FUNGSI HAPUS MASSAL (BULK DELETE)
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:phonebooks,id' // Pastikan nama tabel database sesuai (biasanya phonebooks)
        ]);

        Phonebook::whereIn('id', $request->ids)->delete();

        return back()->with('success', count($request->ids) . ' kontak berhasil dihapus sekaligus.');
    }
    /**
     * MENAMPILKAN HALAMAN EDIT KONTAK
     */
    public function edit($id)
    {
        // Cari data kontak berdasarkan ID
        $phonebook = \App\Models\Phonebook::findOrFail($id);

        // Proteksi: Pastikan hanya pemiliknya (atau Superadmin) yang bisa mengedit
        $user = auth()->user();
        if (!$user->isSuperadmin() && $phonebook->user_id !== $user->getOwnerId()) {
            return redirect()->route('phonebook.index')->with('error', 'Akses ditolak.');
        }

        // Ambil daftar kategori unik untuk pilihan dropdown di form edit
        $categories = \App\Models\Phonebook::where('user_id', $user->getOwnerId())
                        ->whereNotNull('category')
                        ->distinct()
                        ->pluck('category');

        // Arahkan ke file resources/views/phonebook/edit.blade.php
        return view('phonebook.edit', compact('phonebook', 'categories'));
    }
    /**
     * MENGUNDUH TEMPLATE CSV UNTUK IMPORT KONTAK
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=template_import_kontak.csv',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        // TAMBAHKAN KATEGORI DI HEADER EXCEL
        $columns = ['Nama Kontak', 'Nomor WhatsApp', 'Kategori'];

        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');

            fputcsv($file, $columns);

            // Masukkan Data Contoh dengan Kategori
            fputcsv($file, ['Budi Santoso', '081234567890', 'Pelanggan BPS']);
            fputcsv($file, ['Siti Aminah', '628987654321', 'Keluarga']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
