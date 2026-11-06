<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Phonebook;
use App\Models\BroadcastCampaign;
use App\Models\BroadcastRecipient;
use App\Services\BroadcastService;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    /**
     * Tampilkan Daftar Riwayat & Status Antrean Broadcast
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = BroadcastCampaign::with(['device', 'user']);

        if (!$user->isSuperadmin()) {
            $query->where('user_id', $user->getOwnerId());
        }

        $campaigns = $query->latest()->paginate(10)->withQueryString();

        return view('broadcast.index', compact('campaigns'));
    }

    /**
     * Tampilkan Form Pembuatan Broadcast Baru
     */
    public function create()
    {
        $user = auth()->user();
        $ownerId = $user->getOwnerId();

        // 1. Ambil Device (Sesuai Hak Akses & Status Connected)
        if ($user->isSuperadmin()) {
            $devices = Device::where('status', 'connected')->orderBy('name')->get();
        } elseif ($user->isAdmin()) {
            $devices = Device::where('user_id', $user->id)->where('status', 'connected')->orderBy('name')->get();
        } else {
            $devices = $user->handledDevices()->where('status', 'connected')->orderBy('name')->get();
            if ($devices->isEmpty()) {
                $devices = Device::where('user_id', $ownerId)->where('status', 'connected')->orderBy('name')->get();
            }
        }

        // 2. Ambil Daftar Kategori & Jumlah Kontak per Kategori
        $contactsQuery = Phonebook::where('user_id', $ownerId);
        $totalContactsCount = (clone $contactsQuery)->count();

        $categories = (clone $contactsQuery)->whereNotNull('category')
                        ->where('category', '!=', '')
                        ->distinct()
                        ->pluck('category');

        $categoryCounts = (clone $contactsQuery)->whereNotNull('category')
                        ->where('category', '!=', '')
                        ->selectRaw('category, count(*) as count')
                        ->groupBy('category')
                        ->pluck('count', 'category');

        return view('broadcast.create', compact('devices', 'categories', 'totalContactsCount', 'categoryCounts'));
    }

    /**
     * Simpan Broadcast Baru & Mulai Scheduler/Background Sending
     */
    public function store(Request $request, BroadcastService $broadcastService)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'category' => 'required|string',
            'message' => 'required|string',
            'name' => 'nullable|string|max:255',
            'send_mode' => 'required|in:now,scheduled',
            'scheduled_at' => 'nullable|required_if:send_mode,scheduled|date',
            'delay_min' => 'required|integer|min:1|max:300',
            'delay_max' => 'required|integer|min:1|max:300|gte:delay_min',
        ], [
            'device_id.required' => 'Pilih device WhatsApp pengirim.',
            'category.required' => 'Pilih grup/kategori kontak target.',
            'message.required' => 'Isi pesan broadcast tidak boleh kosong.',
            'scheduled_at.required_if' => 'Tentukan tanggal dan jam pengiriman untuk broadcast terjadwal.',
            'delay_max.gte' => 'Jeda maksimal harus lebih besar atau sama dengan jeda minimal.',
        ]);

        $user = auth()->user();
        $ownerId = $user->getOwnerId();
        $device = Device::findOrFail($request->device_id);

        // Ambil semua kontak yang berada di dalam grup tersebut
        $query = Phonebook::where('user_id', $ownerId);

        if ($request->category !== 'all') {
            $query->where('category', $request->category);
        }

        $contacts = $query->get();

        if ($contacts->isEmpty()) {
            return back()->withInput()->with('error', 'Gagal: Tidak ada kontak di dalam grup/kategori yang dipilih.');
        }

        $name = $request->filled('name')
            ? trim($request->name)
            : 'Broadcast ' . ($request->category === 'all' ? 'Semua Kontak' : $request->category) . ' (' . now()->format('d M Y H:i') . ')';

        $isScheduled = ($request->send_mode === 'scheduled') && !empty($request->scheduled_at) && (strtotime($request->scheduled_at) > time());
        $status = $isScheduled ? 'scheduled' : 'pending';
        $scheduledAt = $isScheduled ? $request->scheduled_at : null;

        // 1. Simpan Campaign ke Database
        $campaign = BroadcastCampaign::create([
            'user_id' => $ownerId,
            'device_id' => $device->id,
            'name' => $name,
            'category' => $request->category,
            'message' => $request->message,
            'delay_min' => (int) $request->delay_min,
            'delay_max' => (int) $request->delay_max,
            'scheduled_at' => $scheduledAt,
            'status' => $status,
            'total_recipients' => $contacts->count(),
            'sent_count' => 0,
            'failed_count' => 0,
        ]);

        // 2. Simpan Penerima (Recipients) dengan personalisasi {nama} & {nomor}
        $recipientsData = [];
        $now = now();
        foreach ($contacts as $contact) {
            $recipientName = $contact->name ?: 'Bapak/Ibu';
            $personalizedMessage = str_replace(
                ['{nama}', '{name}', '{nomor}', '{phone}'],
                [$recipientName, $recipientName, $contact->number, $contact->number],
                $request->message
            );

            $recipientsData[] = [
                'broadcast_campaign_id' => $campaign->id,
                'phonebook_id' => $contact->id,
                'name' => $recipientName,
                'number' => $contact->number,
                'message' => $personalizedMessage,
                'status' => 'pending',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($recipientsData) >= 250) {
                BroadcastRecipient::insert($recipientsData);
                $recipientsData = [];
            }
        }

        if (!empty($recipientsData)) {
            BroadcastRecipient::insert($recipientsData);
        }

        // 3. Eksekusi Pengiriman
        if (!$isScheduled) {
            // Jalankan di latar belakang secara asinkron (Non-blocking)
            $broadcastService->dispatchBackground($campaign->id);

            return redirect()->route('broadcast.show', $campaign->id)
                ->with('success', 'Broadcast berhasil disimpan! Pengiriman sedang berjalan di latar belakang dengan jeda aman anti-blokir.');
        }

        return redirect()->route('broadcast.show', $campaign->id)
            ->with('success', "Broadcast berhasil dijadwalkan untuk dikirim pada {$campaign->scheduled_at->format('d M Y H:i')}.");
    }

    /**
     * Tampilkan Detail Status & Laporan Pengiriman Broadcast
     */
    public function show(BroadcastCampaign $campaign, Request $request)
    {
        $user = auth()->user();

        // Otorisasi Akses
        if (!$user->isSuperadmin() && $campaign->user_id !== $user->getOwnerId()) {
            abort(403, 'Akses ditolak.');
        }

        $query = $campaign->recipients();

        // Filter status (sent, failed, pending, processing)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter pencarian nama / nomor
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('number', 'like', "%{$search}%")
                  ->orWhere('error_message', 'like', "%{$search}%");
            });
        }

        $recipients = $query->paginate(25)->withQueryString();

        return view('broadcast.show', compact('campaign', 'recipients'));
    }

    /**
     * Endpoint JSON untuk real-time polling status progres di frontend
     */
    public function statusJson(BroadcastCampaign $campaign)
    {
        $campaign->refresh();

        return response()->json([
            'status' => $campaign->status,
            'total' => $campaign->total_recipients,
            'sent' => $campaign->sent_count,
            'failed' => $campaign->failed_count,
            'pending' => $campaign->pending_count,
            'progress' => $campaign->progress_percentage,
            'is_completed' => $campaign->isCompleted(),
            'completed_at' => $campaign->completed_at ? $campaign->completed_at->format('d M Y H:i:s') : null,
        ]);
    }

    /**
     * Jeda (Pause) Pengiriman Broadcast yang sedang berjalan
     */
    public function pause(BroadcastCampaign $campaign)
    {
        $user = auth()->user();
        if (!$user->isSuperadmin() && $campaign->user_id !== $user->getOwnerId()) {
            abort(403);
        }

        $campaign->update(['status' => 'paused']);

        return back()->with('success', 'Pengiriman broadcast berhasil dijeda (Paused).');
    }

    /**
     * Lanjutkan (Resume) Pengiriman Broadcast
     */
    public function resume(BroadcastCampaign $campaign, BroadcastService $broadcastService)
    {
        $user = auth()->user();
        if (!$user->isSuperadmin() && $campaign->user_id !== $user->getOwnerId()) {
            abort(403);
        }

        $campaign->update(['status' => 'pending']);
        $broadcastService->dispatchBackground($campaign->id);

        return back()->with('success', 'Pengiriman broadcast dilanjutkan di latar belakang.');
    }

    /**
     * Kirim Ulang Pesan yang Berstatus Gagal
     */
    public function retryFailed(BroadcastCampaign $campaign, BroadcastService $broadcastService)
    {
        $user = auth()->user();
        if (!$user->isSuperadmin() && $campaign->user_id !== $user->getOwnerId()) {
            abort(403);
        }

        $failedCount = $campaign->recipients()->where('status', 'failed')->count();
        if ($failedCount === 0) {
            return back()->with('error', 'Tidak ada pesan gagal untuk dikirim ulang.');
        }

        $campaign->recipients()->where('status', 'failed')->update([
            'status' => 'pending',
            'error_message' => null,
        ]);

        $campaign->update([
            'failed_count' => 0,
            'status' => 'pending',
        ]);

        $broadcastService->dispatchBackground($campaign->id);

        return back()->with('success', "{$failedCount} pesan gagal berhasil dimasukkan kembali ke antrean untuk dikirim ulang.");
    }

    /**
     * Hapus Data Broadcast Campaign
     */
    public function destroy(BroadcastCampaign $campaign)
    {
        $user = auth()->user();
        if (!$user->isSuperadmin() && $campaign->user_id !== $user->getOwnerId()) {
            abort(403);
        }

        $campaign->delete();

        return redirect()->route('broadcast.index')->with('success', 'Broadcast berhasil dihapus.');
    }
}
