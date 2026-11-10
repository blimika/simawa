<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Message;
use App\Models\Ticket;
use App\Models\Phonebook;
use App\Services\GowaService;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

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

        // 3. TAMPILKAN SEMUA TIKET DENGAN FITUR SEARCH & FILTER TAB
        $search = $request->input('search');
        $currentTab = $request->input('tab', 'all');

        $ticketsBaseQuery = Ticket::whereIn('device_id', $deviceIds);

        // Hitung total untuk masing-masing tab badge
        $counts = [
            'all'      => (clone $ticketsBaseQuery)->count(),
            'antrean'  => (clone $ticketsBaseQuery)->where('status', 'open')->whereNull('agent_id')->count(),
            'diproses' => (clone $ticketsBaseQuery)->where('status', 'open')->whereNotNull('agent_id')->count(),
            'selesai'  => (clone $ticketsBaseQuery)->where('status', 'closed')->count(),
        ];

        $ticketsQuery = clone $ticketsBaseQuery;

        // Terapkan filter tab
        if ($currentTab === 'antrean') {
            $ticketsQuery->where('status', 'open')->whereNull('agent_id');
        } elseif ($currentTab === 'diproses') {
            $ticketsQuery->where('status', 'open')->whereNotNull('agent_id');
        } elseif ($currentTab === 'selesai') {
            $ticketsQuery->where('status', 'closed');
        }

        // Jika ada input pencarian (berdasarkan Nomor, Nomor Tiket, atau Nama Kontak)
        if (!empty($search)) {
            $ticketsQuery->where(function($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhereIn('number', function($sub) use ($search) {
                      $sub->select('number')
                          ->from('phonebooks')
                          ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Eksekusi query untuk daftar tiket (kiri)
        $tickets = $ticketsQuery->with(['device', 'agent'])
            ->orderBy('has_new_message', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Ambil dan cache avatar profil WhatsApp untuk setiap tiket
        foreach ($tickets as $t) {
            if ($t->device && $t->device->status === 'connected') {
                $cacheKey = "wa_avatar_{$t->device->gowa_session_id}_{$t->number}";
                $avatar = Cache::remember($cacheKey, now()->addHours(6), function () use ($gowa, $t) {
                    $res = $gowa->getUserAvatar($t->device->gowa_session_id, $t->number, true);
                    return $res['results']['url'] ?? 'none';
                });

                if (!empty($avatar) && $avatar !== 'none' && $t->avatar_url !== $avatar) {
                    $t->avatar_url = $avatar;
                    $t->update(['avatar_url' => $avatar]);
                }
            }
        }

        // 4. INISIALISASI DEFAULT UNTUK AREA RUANG CHAT (KANAN)
        $activeTicket = null;
        $messages = [];
        $contactName = 'Pelanggan Baru';

        // 5. JIKA ADA TIKET YANG DIKLIK (Dibuka oleh Agen)
        if ($request->has('ticket_id')) {
            $activeTicket = Ticket::with('device')->findOrFail($request->ticket_id);

            // Hapus tanda pesan baru karena sedang dibaca (Preview)
            if ($activeTicket->has_new_message) {
                $activeTicket->update(['has_new_message' => false]);
            }

            // Pastikan avatar active ticket juga termuat
            if ($activeTicket->device && $activeTicket->device->status === 'connected') {
                $cacheKey = "wa_avatar_{$activeTicket->device->gowa_session_id}_{$activeTicket->number}";
                $avatar = Cache::remember($cacheKey, now()->addHours(6), function () use ($gowa, $activeTicket) {
                    $res = $gowa->getUserAvatar($activeTicket->device->gowa_session_id, $activeTicket->number, true);
                    return $res['results']['url'] ?? 'none';
                });

                if (!empty($avatar) && $avatar !== 'none' && $activeTicket->avatar_url !== $avatar) {
                    $activeTicket->avatar_url = $avatar;
                    $activeTicket->update(['avatar_url' => $avatar]);
                }
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

        // 6. Ambil daftar Agen yang memenuhi syarat untuk dialihkan
        if ($activeTicket && $activeTicket->device) {
            $device = $activeTicket->device;

            // Ambil agen aktif yang ditugaskan ke device ini
            $deviceAgents = $device->agents()->where('is_active', true)->get();

            // Jika belum ada agen khusus yang di-assign ke device, ambil semua agen aktif di bawah admin pemilik device
            if ($deviceAgents->isEmpty()) {
                $deviceAgents = User::where('parent_id', $device->user_id)
                    ->where('role', 'agent')
                    ->where('is_active', true)
                    ->get();
            }

            // Sertakan owner device (Admin) jika aktif
            if ($device->owner && $device->owner->is_active) {
                $deviceAgents->push($device->owner);
            }

            // Jika Super Admin yang login, sertakan semua admin/agen aktif
            if ($user->isSuperadmin()) {
                $allAssignable = User::where('is_active', true)
                    ->whereIn('role', ['agent', 'admin'])
                    ->get();
                $deviceAgents = $deviceAgents->merge($allAssignable);
            }

            // Filter agar tidak menyertakan user yang sedang login dan pastikan unik
            $agents = $deviceAgents->reject(fn($u) => $u->id === $user->id)->unique('id')->values();
        } else {
            $agentsQuery = User::where('is_active', true)->where('id', '!=', $user->id);
            if ($user->isAdmin()) {
                $agentsQuery->where(function($q) use ($user) {
                    $q->where('parent_id', $user->id)->orWhere('id', $user->id);
                });
            } elseif ($user->isAgent()) {
                $agentsQuery->where('parent_id', $user->parent_id)->orWhere('id', $user->parent_id);
            }
            $agents = $agentsQuery->get();
        }

        return view('messages.index', compact('devices', 'tickets', 'activeTicket', 'messages', 'contactName', 'contacts', 'agents', 'counts', 'currentTab'));
    }

    /**
     * MENGIRIM PESAN (BALASAN AGEN) KE PELANGGAN
     */
    public function store(Request $request, GowaService $gowa)
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
                    $ticket = Ticket::where('device_id', $device->id)
                                ->where('number', $cleanNumber)
                                ->where('status', 'open')
                                ->first();

                    if (!$ticket) {
                        Ticket::create([
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

                    Message::create([
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

            // 2. Siapkan & Kirim Pesan Penutup (Hanya jika fitur Salam Penutup diaktifkan untuk device ini)
            $device = $ticket->device;
            if ($device && $device->isClosingEnabled()) {
                $customerName = $ticket->customer_name ?: 'Bapak/Ibu';
                $ticketNumber = $ticket->ticket_number;
                $agentName = auth()->user()->name;

                $closingMessage = $device->getFormattedClosingMessage($customerName, $ticketNumber, $agentName);

                // 3. Kirim Pesan via API GOWA
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

            return redirect()->route('messages.index')->with('success', 'Tiket berhasil ditutup!');
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

        // 2. Kirim pesan notifikasi klaim tiket (Hanya jika fitur Pesan Diambil Agen diaktifkan untuk device ini)
        $device = $ticket->device;
        if ($device && $device->isClaimEnabled()) {
            $customerName = $ticket->customer_name ?: 'Bapak/Ibu';
            $ticketNumber = $ticket->ticket_number;
            $agentName = $user->name;

            $claimMessage = $device->getFormattedClaimMessage($agentName, $customerName, $ticketNumber);

            // 3. Kirim pesan notifikasi ke pelanggan via GOWA
            $response = $gowa->sendMessage(
                $device->gowa_session_id,
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
    /**
     * FUNGSI TRANSFER & AMBIL ALIH TIKET LIVE CHAT
     */
    public function transferTicket(Request $request, Ticket $ticket)
    {
        $currentUser = auth()->user();

        // 1. Cek apakah tiket masih terbuka
        if ($ticket->status !== 'open') {
            return back()->with('error', 'Tiket sudah ditutup dan tidak dapat dialihkan.');
        }

        // 2. Cek hak akses: Superadmin, Admin pemilik device, atau Agen yang bertugas / di device terkait
        $isSuperadmin = $currentUser->isSuperadmin();
        $isAdmin = $currentUser->isAdmin() && $ticket->device && $ticket->device->user_id === $currentUser->id;
        $isCurrentAgent = $ticket->agent_id === $currentUser->id;
        $isDeviceAgent = $ticket->device && $ticket->device->agents()->where('users.id', $currentUser->id)->exists();

        if (!$isSuperadmin && !$isAdmin && !$isCurrentAgent && !$isDeviceAgent && !is_null($ticket->agent_id)) {
            return back()->with('error', 'Anda tidak memiliki hak akses untuk mengalihkan tiket ini.');
        }

        // Tangkap target agent ID baik dari 'target_agent_id' maupun 'agent_id'
        $targetAgentId = $request->input('target_agent_id') ?: $request->input('agent_id');

        // SKENARIO 1: AMBIL ALIH (TAKE OVER)
        if (empty($targetAgentId) || $request->input('action') === 'take_over') {
            $ticket->update(['agent_id' => $currentUser->id]);
            return redirect()->route('messages.index', ['ticket_id' => $ticket->id])
                ->with('success', 'Tiket berhasil Anda ambil alih!');
        }

        // SKENARIO 2: TRANSFER KE AGEN LAIN
        $request->validate([
            'target_agent_id' => 'nullable|exists:users,id',
            'agent_id' => 'nullable|exists:users,id',
        ]);

        $targetAgent = User::findOrFail($targetAgentId);

        $wasReassigned = !is_null($ticket->agent_id);
        $ticket->update(['agent_id' => $targetAgent->id]);

        $actionText = $wasReassigned ? 'dialihkan' : 'ditugaskan';

        // Jika agen mentransfer tiketnya ke orang lain, arahkan ke daftar antrean pesan
        if ($currentUser->isAgent() && $currentUser->id !== $targetAgent->id) {
            return redirect()->route('messages.index')
                ->with('success', "Tiket #{$ticket->ticket_number} berhasil {$actionText} ke {$targetAgent->name}.");
        }

        // Jika admin/superadmin atau ditugaskan ke diri sendiri, tetap buka tiket tersebut
        return redirect()->route('messages.index', ['ticket_id' => $ticket->id])
            ->with('success', "Tiket #{$ticket->ticket_number} berhasil {$actionText} ke {$targetAgent->name}.");
    }

    /**
     * Unduh file media (foto, PDF, dokumen) ke penyimpanan lokal
     */
    public function downloadMedia(Message $message, GowaService $gowa)
    {
        $filePath = $this->ensureMediaLocally($message, $gowa);
        if ($filePath && file_exists($filePath)) {
            $filename = basename($filePath);
            return response()->download($filePath, $filename);
        }

        return back()->with('error', 'File media tidak ditemukan atau gagal diunduh.');
    }

    /**
     * Tampilkan media langsung di browser (inline preview)
     */
    public function viewMedia(Message $message, GowaService $gowa)
    {
        $filePath = $this->ensureMediaLocally($message, $gowa);
        if ($filePath && file_exists($filePath)) {
            return response()->file($filePath);
        }

        abort(404, 'File media tidak ditemukan.');
    }

    /**
     * Memastikan file media tersimpan di disk lokal (public storage)
     */
    protected function ensureMediaLocally(Message $message, GowaService $gowa): ?string
    {
        $user = auth()->user();

        // Validasi hak akses terhadap device
        if (!$user->isSuperadmin()) {
            $device = $message->device;
            if (!$device) {
                return null;
            }
            if ($user->isAdmin() && $device->user_id !== $user->id) {
                return null;
            }
            if ($user->isAgent() && $device->user_id !== $user->parent_id) {
                return null;
            }
        }

        if (empty($message->media_url)) {
            return null;
        }

        $mediaUrl = $message->media_url;
        $isLocal = str_starts_with($mediaUrl, 'received_media/') || str_starts_with($mediaUrl, 'sent_media/');

        if ($isLocal && Storage::disk('public')->exists($mediaUrl)) {
            return Storage::disk('public')->path($mediaUrl);
        }

        // Jika belum lokal atau belum terunduh, unduh langsung dari GOWA
        try {
            $cleanPath = str_replace('\\', '/', $mediaUrl);
            if (str_starts_with($cleanPath, 'http://') || str_starts_with($cleanPath, 'https://')) {
                $downloadUrl = $cleanPath;
            } else {
                $downloadUrl = rtrim($gowa->getBaseUrl(), '/') . '/' . ltrim($cleanPath, '/');
            }

            $response = $gowa->baseClient(25)->get($downloadUrl);
            if ($response->successful()) {
                $extension = pathinfo(parse_url($cleanPath, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'bin';
                $newFilename = 'media_' . time() . '_' . Str::random(5) . '.' . strtolower($extension);
                Storage::disk('public')->put('received_media/' . $newFilename, $response->body());

                $message->update(['media_url' => 'received_media/' . $newFilename]);

                return Storage::disk('public')->path('received_media/' . $newFilename);
            }
        } catch (\Exception $e) {
            \Log::error('Gagal mengambil media dari GOWA di ensureMediaLocally: ' . $e->getMessage());
        }

        return null;
    }
}

