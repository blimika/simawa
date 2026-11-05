<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;

class ApiDocController extends Controller
{
    /**
     * Menampilkan halaman dokumentasi API pengiriman pesan (Khusus Admin & Superadmin)
     */
    public function index()
    {
        $user = auth()->user();

        // Superadmin dapat melihat semua device, Admin hanya melihat device miliknya
        if ($user->isSuperadmin()) {
            $devices = Device::latest()->get();
        } else {
            $devices = Device::where('user_id', $user->id)->latest()->get();
        }

        $baseUrl = url('/');

        return view('api-docs.index', compact('devices', 'baseUrl'));
    }
}
