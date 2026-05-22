<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Phonebook;
use App\Models\Message;
use App\Services\GowaService;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    public function create()
    {
        $user = auth()->user();
        $ownerId = $user->getOwnerId();

        // 1. Ambil Device (Sesuai Hak Akses & Status Connected)
        if ($user->isSuperadmin()) {
            $devices = Device::where('status', 'connected')->get();
        } elseif ($user->isAdmin()) {
            $devices = Device::where('user_id', $user->id)->where('status', 'connected')->get();
        } else {
            $devices = $user->handledDevices()->where('status', 'connected')->get();
        }

        // 2. Ambil Daftar Grup / Kategori
        $categories = Phonebook::where('user_id', $ownerId)
                        ->whereNotNull('category')
                        ->distinct()
                        ->pluck('category');

        return view('broadcast.create', compact('devices', 'categories'));
    }

    public function store(Request $request, GowaService $gowa)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'category' => 'required|string',
            'message' => 'required|string',
        ]);

        $user = auth()->user();
        $ownerId = $user->getOwnerId();
        $device = Device::findOrFail($request->device_id);

        // Ambil semua kontak yang berada di dalam grup tersebut
        $query = Phonebook::where('user_id', $ownerId);

        // Pilihan "Semua Kontak" atau Grup Spesifik
        if ($request->category !== 'all') {
            $query->where('category', $request->category);
        }

        $contacts = $query->get();

        // Jika grup kosong
        if ($contacts->isEmpty()) {
            return back()->with('error', 'Gagal: Tidak ada kontak di dalam grup tersebut.');
        }

        $successCount = 0;
        $failCount = 0;

        // Mulai looping kirim pesan
        foreach ($contacts as $contact) {
            $response = $gowa->sendMessage(
                $device->gowa_session_id,
                $contact->number,
                $request->message
            );

            if ($response->successful()) {
                Message::create([
                    'device_id' => $device->id,
                    'user_id' => $user->id,
                    'number' => $contact->number,
                    'message' => $request->message,
                    'direction' => 'out',
                    'status' => 'sent',
                ]);
                $successCount++;
            } else {
                $failCount++;
            }

            // ANTI-BANNED: Jeda 1,5 Detik setiap mengirim 1 pesan
            // 1500000 micro-seconds = 1.5 detik
            usleep(1500000);
        }

        return back()->with('success', "Broadcast Selesai! Berhasil dikirim ke $successCount nomor, Gagal: $failCount nomor.");
    }
}
