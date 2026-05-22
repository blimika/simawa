<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Device;
use App\Models\Phonebook;
use App\Models\Ticket; // <-- Tambahkan ini
use App\Services\GowaService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // 1. Ambil Device yang diizinkan sesuai Role
        if ($user->isSuperadmin()) {
            $devices = Device::where('status', 'connected')->get();
        } elseif ($user->isAdmin()) {
            $devices = Device::where('user_id', $user->id)->where('status', 'connected')->get();
        } else {
            $devices = $user->handledDevices()->where('status', 'connected')->get();
        }
        $deviceIds = $devices->pluck('id');

        // 2. Ambil Kontak (Phonebook) untuk pencarian dan nama di UI
        $contacts = Phonebook::where('user_id', $user->getOwnerId())->get();

        // 3. TAMPILKAN SEMUA TIKET DENGAN FITUR SEARCH
        $search = $request->input('search');
        $ticketsQuery = Ticket::whereIn('device_id', $deviceIds);

        // Jika ada input pencarian (berdasarkan Nomor, Nomor Tiket, atau Nama Kontak)
        if (!empty($search)) {
            $ticketsQuery->where(function($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%")
                  ->orWhereIn('number', function($sub) use ($search) {
                      $sub->select('number')
                          ->from('phonebooks')
                          ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Eksekusi query untuk daftar tiket (kiri)
        $tickets = $ticketsQuery->orderBy('updated_at', 'desc')->get();

        // =========================================================
        // 4. INISIALISASI DEFAULT UNTUK AREA RUANG CHAT (KANAN)
        // (Ini yang sebelumnya terhapus sehingga memicu ErrorException)
        // =========================================================
        $activeTicket = null;
        $messages = [];
        $contactName = 'Pelanggan Baru';

        // 5. JIKA ADA TIKET YANG DIKLIK (Dibuka oleh Agen)
        if ($request->has('ticket_id')) {
            $activeTicket = Ticket::findOrFail($request->ticket_id);

            // JIKA ADA PESAN BARU, HAPUS TANDANYA KARENA SEKARANG SEDANG DIBACA
            if ($activeTicket->has_new_message) {
                $activeTicket->update(['has_new_message' => false]);
            }

            // Logika klaim tiket HANYA berlaku jika tiket tersebut berstatus 'open'
            if ($activeTicket->status === 'open') {
                if (is_null($activeTicket->agent_id)) {
                    $activeTicket->update(['agent_id' => $user->id]);
                } elseif ($activeTicket->agent_id !== $user->id && !$user->isSuperadmin()) {
                    return redirect()->route('messages.index')->with('error', 'Tiket sudah ditangani agen lain.');
                }
            }

            // Ambil riwayat chat untuk tiket yang sedang aktif
            $messages = Message::where('device_id', $activeTicket->device_id)
                        ->where('number', $activeTicket->number)
                        ->orderBy('created_at', 'asc')
                        ->get();

            // Ubah nama di header chat jika nomor ada di Phonebook
            $phonebook = $contacts->firstWhere('number', $activeTicket->number);

            if ($phonebook) {
                // Prioritas 1: Nama dari Buku Telepon (Kontak tersimpan)
                $contactName = $phonebook->name;
            } else {
                // Prioritas 2: Nama dari Profil WA, jika kosong baru tampil 'Pelanggan Baru'
                $contactName = $activeTicket->customer_name ?: 'Pelanggan Baru';
            }
        }

        // 6. Kirim semua variabel ke file View
        return view('messages.index', compact('devices', 'tickets', 'activeTicket', 'messages', 'contactName', 'contacts'));
    }

    public function store(Request $request, GowaService $gowa)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'number' => 'required|string',
            'message' => 'required|string',
        ]);

        $device = Device::findOrFail($request->device_id);
        $rawNumbers = preg_split('/[\s,]+/', $request->number, -1, PREG_SPLIT_NO_EMPTY);
        $successCount = 0;

        foreach ($rawNumbers as $rawNumber) {
            $targetNumber = preg_replace('/[^0-9]/', '', $rawNumber);
            if (substr($targetNumber, 0, 1) == '0') {
                $targetNumber = '62' . substr($targetNumber, 1);
            }

            if (empty($targetNumber)) continue;

            $response = $gowa->sendMessage(
                $device->gowa_session_id,
                $targetNumber,
                $request->message
            );

            if ($response->successful()) {
                Message::create([
                    'device_id' => $device->id,
                    'user_id' => auth()->id(),
                    'number' => $targetNumber,
                    'message' => $request->message,
                    'direction' => 'out',
                    'status' => 'sent',
                ]);

                // CEK APAKAH TIKET YANG MASIH OPEN SUDAH ADA
                $ticket = Ticket::where('device_id', $device->id)
                            ->where('number', $targetNumber)
                            ->where('status', 'open')
                            ->first();

                if (!$ticket) {
                    // Jika agen membalas pada pelanggan yang tidak memiliki tiket aktif (atau tiket lamanya closed)
                    // Buatkan tiket OPEN baru dan langsung assign ke agen ini
                    Ticket::create([
                        'ticket_number' => 'LYN-' . date('ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4)),
                        'device_id' => $device->id,
                        'number' => $targetNumber,
                        'agent_id' => auth()->id(),
                        'status' => 'open',
                        'has_new_message' => false
                    ]);
                } else {
                    // Jika tiket memang open, cukup update timestamp aktivitas terbaru
                    $ticket->touch();
                }

                $successCount++;
            }

            if (count($rawNumbers) > 1) {
                usleep(1000000);
            }
        }

        if ($successCount > 0) {
            return back()->with('success', "Pesan terkirim!");
        }

        return back()->with('error', "Gagal mengirim pesan.");
    }
    // NAMA FUNGSI DIUBAH MENJADI closeTicket SESUAI DENGAN ROUTE ANDA
    public function closeTicket(Request $request, Ticket $ticket, GowaService $gowa)
    {
        // Pastikan hanya tiket yang masih open yang diproses
        if ($ticket->status === 'open') {

            // 1. Ubah status tiket menjadi closed
            $ticket->update(['status' => 'closed']);

            // 2. Siapkan Pesan Penutup (Otomatis mengambil nama device dari relasi)
            $device = $ticket->device;
            $deviceName = $device ? $device->name : 'SIMAWA';
            $closingMessage = "Terimakasih sudah menggunakan Layanan {$deviceName}.";

            // 3. Kirim Pesan via API GOWA
            // Pastikan device ditemukan dan memiliki gowa_session_id
            if ($device) {
                $response = $gowa->sendMessage(
                    $device->gowa_session_id,
                    $ticket->number,
                    $closingMessage
                );

                // 4. Jika pengiriman berhasil, catat pesan tersebut ke ruang chat
                if ($response->successful()) {
                    Message::create([
                        'device_id' => $ticket->device_id,
                        'user_id' => auth()->id(), // Agen yang menutup tiket
                        'number' => $ticket->number,
                        'message' => $closingMessage,
                        'direction' => 'out',
                        'status' => 'sent',
                    ]);
                }
            }

            return redirect()->route('messages.index')->with('success', 'Tiket ditutup dan pesan penutup otomatis terkirim!');
        }

        return back()->with('error', 'Tiket sudah dalam keadaan tertutup.');
    }
}
