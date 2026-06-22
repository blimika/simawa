<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Message;
use App\Models\Ticket;
use App\Models\Phonebook;
use App\Services\GowaService;

class MessageController extends Controller
{
    /**
     * MENAMPILKAN HALAMAN UTAMA LIVE CHAT
     */
    public function index(Request $request, GowaService $gowa)
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

        // 4. INISIALISASI DEFAULT UNTUK AREA RUANG CHAT (KANAN)
        $activeTicket = null;
        $messages = [];
        $contactName = 'Pelanggan Baru';

        // 5. JIKA ADA TIKET YANG DIKLIK (Dibuka oleh Agen)
        if ($request->has('ticket_id')) {
            $activeTicket = Ticket::findOrFail($request->ticket_id);

            // Hapus tanda pesan baru karena sedang dibaca (Preview)
            if ($activeTicket->has_new_message) {
                $activeTicket->update(['has_new_message' => false]);
            }

            // Ambil riwayat chat untuk tiket yang sedang aktif
            $messages = Message::where('device_id', $activeTicket->device_id)
                        ->where('number', $activeTicket->number)
                        ->orderBy('created_at', 'asc')
                        ->get();

            // Ubah nama di header chat
            $phonebook = $contacts->firstWhere('number', $activeTicket->number);
            if ($phonebook) {
                $contactName = $phonebook->name;
            } else {
                $contactName = $activeTicket->customer_name ?: 'Pelanggan Baru';
            }
        }

        // 6. Kirim semua variabel ke file View
        return view('messages.index', compact('devices', 'tickets', 'activeTicket', 'messages', 'contactName', 'contacts'));
    }

    /**
     * MENGIRIM PESAN (BALASAN AGEN) KE PELANGGAN
     */
    public function store(Request $request, \App\Services\GowaService $gowa)
    {
        $request->validate([
            'device_id' => 'required',
            'number' => 'required',
            'message' => 'nullable|string', 
            'file' => 'nullable|file|max:50000', 
        ]);

        if (empty($request->message) && !$request->hasFile('file')) {
            return back()->with('error', 'Pesan atau lampiran tidak boleh kosong.');
        }

        $device = \App\Models\Device::findOrFail($request->device_id);
        $numbers = array_map('trim', explode(',', $request->number));
        $agentName = auth()->user()->name;

        $baseMessage = $request->message ?? '';
        $messageToSend = $baseMessage ? $baseMessage . "\n\n*({$agentName})*" : "*({$agentName})*";

        $fileContents = null;
        $fileName = null;
        $mediaUrl = null;

        if ($request->hasFile('file')) {
            $uploadedFile = $request->file('file');
            $fileContents = file_get_contents($uploadedFile->getRealPath());
            $fileName = $uploadedFile->getClientOriginalName();
            $mediaUrl = $uploadedFile->store('sent_media', 'public');
        }

        $successCount = 0;
        $lastError = ''; // Menampung error asli dari server GOWA

        foreach ($numbers as $targetNumber) {
            if (empty($targetNumber)) continue;

            // =======================================================
            // 1. AUTO-FORMATTER NOMOR WHATSAPP (Sangat Penting)
            // =======================================================
            // Hilangkan semua karakter selain angka (spasi, strip, tanda +)
            $cleanNumber = preg_replace('/[^0-9]/', '', $targetNumber);
            
            // Jika nomor diawali dengan angka 0, otomatis ganti menjadi 62
            if (str_starts_with($cleanNumber, '0')) {
                $cleanNumber = '62' . substr($cleanNumber, 1);
            }

            // Format final untuk dikirim ke API GOWA
            $formattedPhone = $cleanNumber . '@s.whatsapp.net';

            try {
                // =======================================================
                // 2. PROSES PENGIRIMAN
                // =======================================================
                if ($fileContents) {
                    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $imageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                    
                    if (in_array($extension, $imageExtensions)) {
                        $response = $gowa->sendImage($device->gowa_session_id, $formattedPhone, $fileContents, $fileName, $messageToSend);
                    } else {
                        $response = $gowa->sendFile($device->gowa_session_id, $formattedPhone, $fileContents, $fileName, $messageToSend);
                    }
                } else {
                    $response = $gowa->sendMessage($device->gowa_session_id, $formattedPhone, $messageToSend);
                }

                // =======================================================
                // 3. CEK HASIL RESPON DARI GOWA
                // =======================================================
                if ($response && $response->successful()) {
                    
                    // Simpan ke Tiket & Messages menggunakan nomor yang sudah bersih (62...)
                    $ticket = \App\Models\Ticket::where('device_id', $device->id)
                                ->where('number', $cleanNumber)
                                ->where('status', 'open')
                                ->first();

                    if (!$ticket) {
                        \App\Models\Ticket::create([
                            'ticket_number' => 'LYN-' . date('ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(4)),
                            'device_id' => $device->id,
                            'number' => $cleanNumber,
                            'agent_id' => auth()->id(),
                            'tag' => 'umum',
                            'status' => 'open',
                            'has_new_message' => false
                        ]);
                    } else {
                        $ticket->touch(); 
                    }

                    \App\Models\Message::create([
                        'device_id' => $device->id,
                        'user_id' => auth()->id(),
                        'number' => $cleanNumber,
                        'message' => $messageToSend,
                        'media_url' => $mediaUrl,
                        'direction' => 'out',
                        'status' => 'sent',
                    ]);

                    $successCount++;
                } else {
                    // Jika gagal, tangkap dan terjemahkan apa alasan GOWA menolaknya
                    $lastError = $response ? ($response->json('message') ?? $response->body()) : 'Server GOWA tidak memberikan respon';
                }
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
            }
        }

        if ($successCount > 0) {
            return back()->with('success', "Pesan berhasil dikirim.");
        }

        // Tampilkan pesan error yang sesungguhnya ke layar Admin
        return back()->with('error', 'Gagal mengirim pesan. GOWA membalas: ' . $lastError);
    }

    /**
     * MENANDAI TIKET SELESAI (CLOSED) & AUTO-REPLY PENUTUP
     */
    public function closeTicket(Request $request, Ticket $ticket, GowaService $gowa)
    {
        // Pastikan hanya tiket yang masih open yang diproses
        if ($ticket->status === 'open') {

            // 1. Ubah status tiket menjadi closed
            $ticket->update(['status' => 'closed']);

            // 2. Siapkan Pesan Penutup (Otomatis mengambil nama device dari relasi)
            $device = $ticket->device;
            $deviceName = $device ? $device->name : 'SIMAWA';
            $customerName = $ticket->customer_name ? $ticket->customer_name : 'Bapak/Ibu';
            $ticketNumber = $ticket->ticket_number;

            $closingMessage =   "Terima kasih Bapak/Ibu {$customerName}.\n"
                              . "Nomor tiket {$ticketNumber} telah kami tandai selesai.\n\n"
                              . "Semoga informasi/layanan yang diberikan bermanfaat.\n"
                              . "Apabila Bapak/Ibu {$customerName} membutuhkan layanan kembali, "
                              . "silakan mengirim pesan baru melalui WhatsApp ini.\n\n"
                              . "Salam hangat,\n"
                              . "Tim Layanan WhatsApp {$deviceName}.";

            // 3. Kirim Pesan via API GOWA
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

    /**
     * MENGUBAH KATEGORI / TAG PADA TIKET
     */
    public function updateTag(Request $request, Ticket $ticket)
    {
        $request->validate([
            'tag' => 'required|string'
        ]);

        $ticket->update(['tag' => $request->tag]);

        return back()->with('success', 'Tag kategori tiket berhasil diubah!');
    }
    /**
     * AGEN MEMULAI LAYANAN (MENGKLAIM TIKET)
     */
    public function startService(Request $request, Ticket $ticket, GowaService $gowa)
    {
        $user = auth()->user();

        if ($ticket->status !== 'open') {
            return back()->with('error', 'Tiket sudah ditutup.');
        }

        if (!is_null($ticket->agent_id)) {
            return back()->with('error', 'Maaf, tiket ini baru saja diambil oleh agen lain.');
        }

        // 1. Klaim tiket ke agen yang mengeklik
        $ticket->update(['agent_id' => $user->id]);

        // 2. Siapkan pesan otomatis
        $claimMessage = "Permintaan/tiket Bapak/Ibu saat ini sedang dilayani oleh Operator Kami *({$user->name})*.";

        // 3. Kirim pesan notifikasi ke pelanggan via GOWA
        if ($ticket->device) {
            $response = $gowa->sendMessage(
                $ticket->device->gowa_session_id,
                $ticket->number,
                $claimMessage
            );

            if ($response->successful()) {
                Message::create([
                    'device_id' => $ticket->device_id,
                    'user_id' => $user->id,
                    'number' => $ticket->number,
                    'message' => $claimMessage,
                    'direction' => 'out',
                    'status' => 'sent',
                ]);
            }
        }

        return back()->with('success', 'Tiket berhasil diambil. Anda sudah bisa membalas pesan pelanggan.');
    }
}

