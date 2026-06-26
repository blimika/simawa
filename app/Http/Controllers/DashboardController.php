<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\User;
use App\Models\Message; // Pastikan model Message dipanggil
use Carbon\Carbon;      // Untuk mendeteksi tanggal hari ini

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'superadmin') {
            // ==========================================
            // STATISTIK SUPERADMIN (Melihat Semua Data)
            // ==========================================
            $totalDevice = Device::count();
            $totalAgent = User::where('role', 'agent')->count();
            $pesanHariIni = Message::whereDate('created_at', Carbon::today())->count();

            return view('dashboard.superadmin', compact('totalDevice', 'totalAgent', 'pesanHariIni'));

        } elseif ($user->role === 'admin') {
            // ==========================================
            // STATISTIK ADMIN (Hanya melihat miliknya sendiri)
            // ==========================================
            $totalDevice = Device::where('user_id', $user->id)->count();

            // Hitung agen yang parent_id nya adalah Admin ini
            $totalAgent = User::where('role', 'agent')->where('parent_id', $user->id)->count();

            // Hitung pesan masuk hari ini berdasarkan device milik Admin ini
            $deviceIds = Device::where('user_id', $user->id)->pluck('id');
            $pesanHariIni = Message::whereIn('device_id', $deviceIds)
                                   ->whereDate('created_at', Carbon::today())
                                   ->count();

            return view('dashboard.admin', compact('totalDevice', 'totalAgent', 'pesanHariIni'));

        } else {
            // ==========================================
            // TAMPILAN AGEN
            // ==========================================
            // Agen tidak butuh statistik global, arahkan ke dashboardnya
            return view('dashboard.agent');
        }
    }
}
