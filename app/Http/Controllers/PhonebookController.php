<?php

namespace App\Http\Controllers;

use App\Models\Phonebook;
use App\Models\Device;
use App\Services\ExcelSpreadsheetService;
use App\Services\GowaService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class PhonebookController extends Controller
{
    public function index(Request $request, GowaService $gowa)
    {
        $user = auth()->user();
        $ownerId = $user->getOwnerId();
        $tab = $request->input('tab', 'phonebook');

        // Mengambil daftar kategori unik untuk dropdown
        $categories = Phonebook::where('user_id', $ownerId)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        // 1. DATA UNTUK TAB BUKU TELEPON SIMAWA
        $phonebookSearch = $request->input('search');
        $query = Phonebook::query();

        if (!$user->isSuperadmin()) {
            $query->where('user_id', $ownerId);
        }

        if (!empty($phonebookSearch)) {
            $query->where(function ($q) use ($phonebookSearch) {
                $q->where('name', 'like', "%{$phonebookSearch}%")
                  ->orWhere('number', 'like', "%{$phonebookSearch}%")
                  ->orWhere('category', 'like', "%{$phonebookSearch}%");
            });
        }

        $contacts = $query->with('owner')->latest()->paginate(15, ['*'], 'page')->withQueryString();

        // 2. DATA UNTUK TAB KONTAK WHATSAPP (GOWA API: /user/my/contacts)
        if ($user->isSuperadmin()) {
            $devices = Device::orderBy('name', 'asc')->get();
        } elseif ($user->isAdmin()) {
            $devices = Device::where('user_id', $user->id)->orderBy('name', 'asc')->get();
        } else {
            $devices = $user->handledDevices()->orderBy('name', 'asc')->get();
            if ($devices->isEmpty()) {
                $devices = Device::where('user_id', $ownerId)->orderBy('name', 'asc')->get();
            }
        }

        $selectedDevice = null;
        if ($devices->isNotEmpty()) {
            if ($request->filled('device_id')) {
                $selectedDevice = $devices->firstWhere('id', $request->device_id);
            }
            if (!$selectedDevice) {
                // Utamakan device dengan status connected
                $selectedDevice = $devices->firstWhere('status', 'connected') ?: $devices->first();
            }
        }

        $waContacts = null;
        $totalWaContacts = 0;
        $waError = null;

        if ($tab === 'whatsapp' && $selectedDevice) {
            $cacheKey = "wa_contacts_device_{$selectedDevice->id}";
            if ($request->boolean('refresh_wa')) {
                Cache::forget($cacheKey);
            }

            try {
                $waResponse = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($gowa, $selectedDevice) {
                    return $gowa->getMyContacts($selectedDevice->gowa_session_id);
                });

                if (!$waResponse) {
                    Cache::forget($cacheKey);
                    $waError = 'Tidak ada respon dari server WhatsApp Gateway. Pastikan server GOWA sedang berjalan.';
                } elseif (isset($waResponse['code']) && $waResponse['code'] !== 'SUCCESS') {
                    Cache::forget($cacheKey);
                    $waError = $waResponse['message'] ?? 'Gagal mengambil kontak WhatsApp dari device ini.';
                } else {
                    $rawContacts = $waResponse['results']['data'] ?? $waResponse['results'] ?? [];
                    if (!is_array($rawContacts)) {
                        $rawContacts = [];
                    }

                    // Ambil kontak phonebook yang sudah ada untuk pengecekan cepat (O(1))
                    $savedPhonebooks = Phonebook::where('user_id', $ownerId)
                        ->get(['id', 'number', 'name', 'category'])
                        ->keyBy('number');

                    $parsedList = [];
                    foreach ($rawContacts as $item) {
                        $jid = $item['jid'] ?? '';
                        // Filter obrolan grup atau broadcast
                        if (str_ends_with($jid, '@g.us') || str_contains($jid, 'broadcast')) {
                            continue;
                        }

                        $jidPrefix = explode('@', $jid)[0] ?? '';
                        $cleanNumber = preg_replace('/[^0-9]/', '', $jidPrefix);
                        if (empty($cleanNumber)) {
                            continue;
                        }
                        if (str_starts_with($cleanNumber, '0')) {
                            $cleanNumber = '62' . substr($cleanNumber, 1);
                        }

                        $contactName = trim($item['name'] ?? '');
                        $isSaved = $savedPhonebooks->has($cleanNumber);
                        $savedRecord = $isSaved ? $savedPhonebooks->get($cleanNumber) : null;

                        $parsedList[] = [
                            'jid' => $jid,
                            'number' => $cleanNumber,
                            'name' => $contactName ?: ('+' . $cleanNumber),
                            'raw_name' => $contactName,
                            'is_saved' => $isSaved,
                            'saved_record' => $savedRecord,
                        ];
                    }

                    // Pencarian nama / nomor pada Kontak WA
                    $searchWa = trim($request->input('search_wa', ''));
                    if (!empty($searchWa)) {
                        $parsedList = array_values(array_filter($parsedList, function ($c) use ($searchWa) {
                            return stripos($c['name'], $searchWa) !== false ||
                                   stripos($c['number'], $searchWa) !== false;
                        }));
                    }

                    $totalWaContacts = count($parsedList);

                    // Pagination khusus kontak WA
                    $perPage = 25;
                    $currentPage = LengthAwarePaginator::resolveCurrentPage('wa_page');
                    $currentItems = array_slice($parsedList, ($currentPage - 1) * $perPage, $perPage);

                    $waContacts = new LengthAwarePaginator(
                        $currentItems,
                        $totalWaContacts,
                        $perPage,
                        $currentPage,
                        [
                            'path' => LengthAwarePaginator::resolveCurrentPath(),
                            'pageName' => 'wa_page',
                            'query' => $request->query(),
                        ]
                    );
                }
            } catch (\Exception $e) {
                Cache::forget($cacheKey);
                $waError = 'Terjadi kesalahan saat memproses kontak WhatsApp: ' . $e->getMessage();
            }
        }

        return view('phonebook.index', compact(
            'tab',
            'contacts',
            'categories',
            'devices',
            'selectedDevice',
            'waContacts',
            'totalWaContacts',
            'waError'
        ));
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
        $user = auth()->user();

        if (!$user->isSuperadmin() && $phonebook->user_id !== $user->getOwnerId()) {
            return redirect()->route('phonebook.index')->with('error', 'Akses ditolak.');
        }

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
     * FUNGSI IMPORT EXCEL (.XLS SAJA) KE PHONEBOOK
     */
    public function import(Request $request, ExcelSpreadsheetService $excel)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:5120',
                function ($attribute, $value, $fail) {
                    if (strtolower($value->getClientOriginalExtension()) !== 'xls') {
                        $fail('File harus berformat .xls.');
                    }
                },
            ],
        ]);

        $file = $request->file('file');
        try {
            $rows = $excel->parseRows($file->getRealPath(), 'xls', true);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file Excel .xls: ' . $e->getMessage());
        }

        if (empty($rows)) {
            return back()->with('error', 'File Excel .xls kosong atau tidak memiliki baris data.');
        }

        // Lewati baris pertama jika berisi header judul kolom
        $firstRowText = strtolower(implode(' ', $rows[0] ?? []));
        if (
            str_contains($firstRowText, 'nama') ||
            str_contains($firstRowText, 'nomor') ||
            str_contains($firstRowText, 'whatsapp') ||
            str_contains($firstRowText, 'kategori')
        ) {
            array_shift($rows);
        }

        if (empty($rows)) {
            return back()->with('error', 'Tidak ditemukan baris data kontak di dalam file Excel.');
        }

        $importedCount = 0;
        $skippedCount = 0;
        $ownerId = auth()->user()->getOwnerId();

        foreach ($rows as $row) {
            $rawName = trim((string) ($row[0] ?? ''));
            $rawNumber = trim((string) ($row[1] ?? ''));
            $category = !empty($row[2]) ? trim((string) $row[2]) : null;

            if ($rawNumber === '') {
                continue;
            }

            $cleanNumber = preg_replace('/[^0-9]/', '', $rawNumber);
            if (str_starts_with($cleanNumber, '0')) {
                $cleanNumber = '62' . substr($cleanNumber, 1);
            }

            if (strlen($cleanNumber) < 6) {
                $skippedCount++;
                continue;
            }

            $name = $rawName !== '' ? $rawName : 'Tanpa Nama';

            Phonebook::updateOrCreate(
                [
                    'number' => $cleanNumber,
                    'user_id' => $ownerId,
                ],
                [
                    'name' => $name,
                    'category' => $category,
                ]
            );

            $importedCount++;
        }

        if ($importedCount === 0) {
            return back()->with('error', 'Tidak ada kontak valid yang berhasil diimpor dari file .xls.');
        }

        $message = "Berhasil mengimpor {$importedCount} kontak dari file .xls ke Buku Telepon.";
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} baris dilewati karena nomor tidak valid.)";
        }

        return back()->with('success', $message);
    }

    /**
     * FUNGSI HAPUS MASSAL (BULK DELETE)
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:phonebooks,id',
        ]);

        $user = auth()->user();
        $query = Phonebook::whereIn('id', $request->ids);
        if (!$user->isSuperadmin()) {
            $query->where('user_id', $user->getOwnerId());
        }

        $deletedCount = $query->delete();

        return back()->with('success', "{$deletedCount} kontak berhasil dihapus sekaligus.");
    }

    /**
     * MENAMPILKAN HALAMAN EDIT KONTAK
     */
    public function edit($id)
    {
        // Cari data kontak berdasarkan ID
        $phonebook = Phonebook::findOrFail($id);

        // Proteksi: Pastikan hanya pemiliknya (atau Superadmin) yang bisa mengedit
        $user = auth()->user();
        if (!$user->isSuperadmin() && $phonebook->user_id !== $user->getOwnerId()) {
            return redirect()->route('phonebook.index')->with('error', 'Akses ditolak.');
        }

        // Ambil daftar kategori unik untuk pilihan dropdown di form edit
        $categories = Phonebook::where('user_id', $user->getOwnerId())
                        ->whereNotNull('category')
                        ->distinct()
                        ->pluck('category');

        // Arahkan ke file resources/views/phonebook/edit.blade.php
        return view('phonebook.edit', compact('phonebook', 'categories'));
    }

    /**
     * MENGUNDUH TEMPLATE EXCEL (.XLS) UNTUK IMPORT KONTAK
     */
    public function downloadTemplate(ExcelSpreadsheetService $excel)
    {
        $rows = [
            ['Nama Kontak', 'Nomor WhatsApp', 'Kategori'],
            ['Budi Santoso', '081234567890', 'Pelanggan BPS'],
            ['Siti Aminah', '628987654321', 'Keluarga'],
        ];

        $binary = $excel->generateBiff8Xls($rows, 'Template Kontak');

        return response($binary, 200, [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="template_import_kontak.xls"',
            'Pragma'              => 'public',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    /**
     * SIMPAN 1 KONTAK WHATSAPP KE PHONEBOOK
     */
    public function saveWaContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:30',
            'category' => 'nullable|string|max:100',
        ]);

        $cleanNumber = preg_replace('/[^0-9]/', '', $request->number);
        if (str_starts_with($cleanNumber, '0')) {
            $cleanNumber = '62' . substr($cleanNumber, 1);
        }

        $ownerId = auth()->user()->getOwnerId();

        Phonebook::updateOrCreate(
            [
                'number' => $cleanNumber,
                'user_id' => $ownerId,
            ],
            [
                'name' => $request->name,
                'category' => $request->filled('category') ? trim($request->category) : null,
            ]
        );

        return back()->with('success', "Kontak \"{$request->name}\" ({$cleanNumber}) berhasil disimpan ke Buku Telepon SIMAWA.");
    }

    /**
     * IMPORT KONTAK TERPILIH DARI WHATSAPP KE PHONEBOOK
     */
    public function importWaContacts(Request $request)
    {
        $request->validate([
            'contacts' => 'required|array|min:1',
            'contacts.*.name' => 'nullable|string|max:255',
            'contacts.*.number' => 'required|string|max:30',
            'category' => 'nullable|string|max:100',
        ]);

        $ownerId = auth()->user()->getOwnerId();
        $category = $request->filled('category') ? trim($request->category) : null;
        $importedCount = 0;

        foreach ($request->input('contacts') as $item) {
            $rawNumber = $item['number'] ?? '';
            $name = !empty(trim($item['name'] ?? '')) ? trim($item['name']) : 'Kontak WA ' . $rawNumber;

            $cleanNumber = preg_replace('/[^0-9]/', '', $rawNumber);
            if (empty($cleanNumber)) continue;
            if (str_starts_with($cleanNumber, '0')) {
                $cleanNumber = '62' . substr($cleanNumber, 1);
            }

            Phonebook::updateOrCreate(
                [
                    'number' => $cleanNumber,
                    'user_id' => $ownerId,
                ],
                [
                    'name' => $name,
                    'category' => $category,
                ]
            );
            $importedCount++;
        }

        return back()->with('success', "Berhasil menyimpan {$importedCount} kontak WhatsApp terpilih ke Buku Telepon SIMAWA.");
    }

    /**
     * IMPORT SELURUH KONTAK DARI DEVICE WHATSAPP KE PHONEBOOK
     */
    public function importAllWaContacts(Request $request, GowaService $gowa)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'category' => 'nullable|string|max:100',
        ]);

        $device = Device::findOrFail($request->device_id);
        $user = auth()->user();

        // Keamanan akses device
        if ($user->isAdmin() && $device->user_id !== $user->id) {
            return back()->with('error', 'Akses ke device ini ditolak.');
        }

        $res = $gowa->getMyContacts($device->gowa_session_id);
        $rawList = $res['results']['data'] ?? $res['results'] ?? [];

        if (empty($rawList) || !is_array($rawList)) {
            return back()->with('error', 'Tidak ada data kontak yang ditemukan dari device WhatsApp.');
        }

        $ownerId = $user->getOwnerId();
        $category = $request->filled('category') ? trim($request->category) : null;
        $importedCount = 0;

        foreach ($rawList as $item) {
            $jid = $item['jid'] ?? '';
            if (str_ends_with($jid, '@g.us') || str_contains($jid, 'broadcast')) continue;

            $jidPrefix = explode('@', $jid)[0] ?? '';
            $cleanNumber = preg_replace('/[^0-9]/', '', $jidPrefix);
            if (empty($cleanNumber)) continue;
            if (str_starts_with($cleanNumber, '0')) {
                $cleanNumber = '62' . substr($cleanNumber, 1);
            }

            $name = !empty(trim($item['name'] ?? '')) ? trim($item['name']) : '+' . $cleanNumber;

            Phonebook::updateOrCreate(
                [
                    'number' => $cleanNumber,
                    'user_id' => $ownerId,
                ],
                [
                    'name' => $name,
                    'category' => $category,
                ]
            );
            $importedCount++;
        }

        return back()->with('success', "Berhasil mengimpor seluruh {$importedCount} kontak WhatsApp dari device \"{$device->name}\" ke Buku Telepon SIMAWA.");
    }
}
