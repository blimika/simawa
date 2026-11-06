<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class AutoReplyController extends Controller
{
    /**
     * Tampilkan halaman konfigurasi Pesan Otomatis Live Chat
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $devicesQuery = Device::query();
        if ($user->isAdmin()) {
            $devicesQuery->where('user_id', $user->id);
        }

        $devices = $devicesQuery->orderBy('name', 'asc')->get();

        $selectedDevice = null;
        if ($devices->isNotEmpty()) {
            if ($request->has('device_id')) {
                $selectedDevice = $devices->firstWhere('id', $request->device_id);
            }
            if (!$selectedDevice) {
                $selectedDevice = $devices->first();
            }
        }

        return view('auto-replies.index', compact('devices', 'selectedDevice'));
    }

    /**
     * Simpan perubahan template pesan otomatis untuk device
     */
    public function update(Request $request, Device $device)
    {
        $user = auth()->user();

        // Keamanan: Pastikan Admin hanya bisa mengubah device miliknya sendiri
        if ($user->isAdmin() && $device->user_id !== $user->id) {
            return redirect()->route('auto-replies.index')->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'greeting_message' => ['nullable', 'string'],
            'claim_message'    => ['nullable', 'string'],
            'closing_message'   => ['nullable', 'string'],
            'apply_to_all'     => ['nullable', 'boolean'],
        ]);

        $data = [
            'greeting_message' => $request->greeting_message,
            'claim_message'    => $request->claim_message,
            'closing_message'   => $request->closing_message,
        ];

        $device->update($data);

        // Jika opsi "Terapkan ke seluruh device saya" dicentang
        if ($request->boolean('apply_to_all')) {
            $ownerId = $user->isAdmin() ? $user->id : $device->user_id;
            Device::where('user_id', $ownerId)->update($data);

            return redirect()->route('auto-replies.index', ['device_id' => $device->id])
                ->with('success', 'Pesan otomatis berhasil disimpan dan diterapkan ke SEMUA device WhatsApp Anda.');
        }

        return redirect()->route('auto-replies.index', ['device_id' => $device->id])
            ->with('success', 'Pesan otomatis untuk device ' . $device->name . ' berhasil diperbarui.');
    }

    /**
     * Reset template pesan ke pengaturan bawaan sistem (default)
     */
    public function reset(Request $request, Device $device)
    {
        $user = auth()->user();

        if ($user->isAdmin() && $device->user_id !== $user->id) {
            return redirect()->route('auto-replies.index')->with('error', 'Akses ditolak.');
        }

        $type = $request->input('type', 'all');

        if ($type === 'greeting') {
            $device->update(['greeting_message' => null]);
            $msg = 'Salam Pembuka berhasil di-reset ke standar.';
        } elseif ($type === 'claim') {
            $device->update(['claim_message' => null]);
            $msg = 'Pesan Diambil Agen berhasil di-reset ke standar.';
        } elseif ($type === 'closing') {
            $device->update(['closing_message' => null]);
            $msg = 'Salam Penutup berhasil di-reset ke standar.';
        } else {
            $device->update([
                'greeting_message' => null,
                'claim_message'    => null,
                'closing_message'   => null,
            ]);
            $msg = 'Semua pesan otomatis berhasil di-reset ke standar sistem.';
        }

        return redirect()->route('auto-replies.index', ['device_id' => $device->id])
            ->with('success', $msg);
    }
}
