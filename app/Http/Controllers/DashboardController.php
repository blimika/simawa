<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Message;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();

        if ($user->isSuperadmin()) {
            // ==========================================
            // STATISTIK SUPERADMIN (Melihat Semua Data)
            // ==========================================
            $devices = Device::with('owner')->get();
            $deviceIds = $devices->pluck('id');

            $totalDevice = $devices->count();
            $connectedDevices = $devices->where('status', 'connected')->count();
            $disconnectedDevices = $devices->where('status', '!=', 'connected')->count();

            $totalAdmin = User::where('role', 'admin')->count();
            $totalAgent = User::where('role', 'agent')->count();

            // Statistik Tiket / Layanan Seluruh Sistem
            $tiketBelumDiambil = Ticket::where('status', 'open')->whereNull('agent_id')->count();
            $tiketSedangDilayani = Ticket::where('status', 'open')->whereNotNull('agent_id')->count();
            $totalTiketOpen = Ticket::where('status', 'open')->count();
            $tiketSelesaiHariIni = Ticket::whereIn('status', ['closed', 'resolved'])->whereDate('updated_at', $today)->count();
            $tiketSelesaiTotal = Ticket::whereIn('status', ['closed', 'resolved'])->count();

            // Statistik Pesan Seluruh Sistem
            $pesanMasukHariIni = Message::where('direction', 'in')->whereDate('created_at', $today)->count();
            $pesanKeluarHariIni = Message::where('direction', 'out')->whereDate('created_at', $today)->count();
            $pesanHariIni = $pesanMasukHariIni + $pesanKeluarHariIni;

            // Antrean Tiket Terkini (Belum diambil atau sedang aktif)
            $antreanTiket = Ticket::where('status', 'open')
                ->with(['device', 'agent'])
                ->orderBy('has_new_message', 'desc')
                ->orderBy('updated_at', 'desc')
                ->take(6)
                ->get();

            // Tiket Terakhir Selesai
            $tiketSelesaiTerbaru = Ticket::whereIn('status', ['closed', 'resolved'])
                ->with(['device', 'agent'])
                ->orderBy('updated_at', 'desc')
                ->take(5)
                ->get();

            return view('dashboard.superadmin', compact(
                'devices', 'totalDevice', 'connectedDevices', 'disconnectedDevices',
                'totalAdmin', 'totalAgent',
                'tiketBelumDiambil', 'tiketSedangDilayani', 'totalTiketOpen',
                'tiketSelesaiHariIni', 'tiketSelesaiTotal',
                'pesanMasukHariIni', 'pesanKeluarHariIni', 'pesanHariIni',
                'antreanTiket', 'tiketSelesaiTerbaru'
            ));

        } elseif ($user->isAdmin()) {
            // ==========================================
            // STATISTIK ADMIN (Tenant / Instansi Admin)
            // ==========================================
            $devices = Device::where('user_id', $user->id)->get();
            $deviceIds = $devices->pluck('id');

            $totalDevice = $devices->count();
            $connectedDevices = $devices->where('status', 'connected')->count();
            $disconnectedDevices = $devices->where('status', '!=', 'connected')->count();

            // Hitung agen yang parent_id nya adalah Admin ini
            $totalAgent = User::where('role', 'agent')->where('parent_id', $user->id)->count();

            // Statistik Tiket Layanan
            $tiketBelumDiambil = Ticket::whereIn('device_id', $deviceIds)
                ->where('status', 'open')
                ->whereNull('agent_id')
                ->count();

            $tiketSedangDilayani = Ticket::whereIn('device_id', $deviceIds)
                ->where('status', 'open')
                ->whereNotNull('agent_id')
                ->count();

            $totalTiketOpen = Ticket::whereIn('device_id', $deviceIds)
                ->where('status', 'open')
                ->count();

            $tiketSelesaiHariIni = Ticket::whereIn('device_id', $deviceIds)
                ->whereIn('status', ['closed', 'resolved'])
                ->whereDate('updated_at', $today)
                ->count();

            $tiketSelesaiTotal = Ticket::whereIn('device_id', $deviceIds)
                ->whereIn('status', ['closed', 'resolved'])
                ->count();

            // Hitung pesan hari ini berdasarkan device milik Admin ini
            $pesanMasukHariIni = Message::whereIn('device_id', $deviceIds)
                ->where('direction', 'in')
                ->whereDate('created_at', $today)
                ->count();

            $pesanKeluarHariIni = Message::whereIn('device_id', $deviceIds)
                ->where('direction', 'out')
                ->whereDate('created_at', $today)
                ->count();

            $pesanHariIni = $pesanMasukHariIni + $pesanKeluarHariIni;

            // Kinerja Agen di Bawah Admin Ini
            $agentsPerformance = User::where('role', 'agent')
                ->where('parent_id', $user->id)
                ->with(['handledDevices'])
                ->get()
                ->map(function ($agent) use ($deviceIds, $today) {
                    $openCount = Ticket::whereIn('device_id', $deviceIds)
                        ->where('agent_id', $agent->id)
                        ->where('status', 'open')
                        ->count();

                    $closedCount = Ticket::whereIn('device_id', $deviceIds)
                        ->where('agent_id', $agent->id)
                        ->whereIn('status', ['closed', 'resolved'])
                        ->count();

                    $closedToday = Ticket::whereIn('device_id', $deviceIds)
                        ->where('agent_id', $agent->id)
                        ->whereIn('status', ['closed', 'resolved'])
                        ->whereDate('updated_at', $today)
                        ->count();

                    $messagesToday = Message::where('user_id', $agent->id)
                        ->whereDate('created_at', $today)
                        ->count();

                    return [
                        'agent' => $agent,
                        'open_count' => $openCount,
                        'closed_count' => $closedCount,
                        'closed_today' => $closedToday,
                        'messages_today' => $messagesToday,
                    ];
                });

            // Antrean Tiket Terkini (Belum diambil atau sedang aktif)
            $antreanTiket = Ticket::whereIn('device_id', $deviceIds)
                ->where('status', 'open')
                ->with(['device', 'agent'])
                ->orderBy('has_new_message', 'desc')
                ->orderBy('updated_at', 'desc')
                ->take(6)
                ->get();

            // Tiket Terakhir Selesai
            $tiketSelesaiTerbaru = Ticket::whereIn('device_id', $deviceIds)
                ->whereIn('status', ['closed', 'resolved'])
                ->with(['device', 'agent'])
                ->orderBy('updated_at', 'desc')
                ->take(5)
                ->get();

            return view('dashboard.admin', compact(
                'devices', 'totalDevice', 'connectedDevices', 'disconnectedDevices',
                'totalAgent',
                'tiketBelumDiambil', 'tiketSedangDilayani', 'totalTiketOpen',
                'tiketSelesaiHariIni', 'tiketSelesaiTotal',
                'pesanMasukHariIni', 'pesanKeluarHariIni', 'pesanHariIni',
                'agentsPerformance', 'antreanTiket', 'tiketSelesaiTerbaru'
            ));

        } else {
            // ==========================================
            // STATISTIK AGEN
            // ==========================================
            // 1. Ambil Device yang ditugaskan ke Agen ini (atau milik supervisor jika belum dibatasi)
            $devices = $user->handledDevices()->get();
            if ($devices->isEmpty()) {
                $devices = Device::where('user_id', $user->parent_id)->get();
            }
            $deviceIds = $devices->pluck('id');

            // 2. Statistik Khusus Agen
            // - Pesan / Tiket Belum Terlayani (Antrean baru yang belum diambil siapa pun)
            $tiketBelumDiambil = Ticket::whereIn('device_id', $deviceIds)
                ->where('status', 'open')
                ->whereNull('agent_id')
                ->count();

            // - Sedang Dilayani oleh Agen yang sedang login ini
            $tiketSayaAktif = Ticket::whereIn('device_id', $deviceIds)
                ->where('status', 'open')
                ->where('agent_id', $user->id)
                ->count();

            // - Total Tiket Belum Selesai (Open) di seluruh Device yang diakses
            $totalBelumTerlayani = Ticket::whereIn('device_id', $deviceIds)
                ->where('status', 'open')
                ->count();

            // - Sudah Terlayani & Selesai oleh Agen ini Hari Ini
            $tiketSayaSelesaiHariIni = Ticket::where('agent_id', $user->id)
                ->whereIn('status', ['closed', 'resolved'])
                ->whereDate('updated_at', $today)
                ->count();

            // - Total Sudah Terlayani & Selesai oleh Agen ini Sepanjang Waktu
            $tiketSayaSelesaiTotal = Ticket::where('agent_id', $user->id)
                ->whereIn('status', ['closed', 'resolved'])
                ->count();

            // - Balasan Pesan yang Dikirim oleh Agen ini Hari Ini
            $pesanSayaHariIni = Message::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->count();

            // - Total Pesan Masuk Hari Ini pada Device yang Ditangani
            $pesanMasukHariIni = Message::whereIn('device_id', $deviceIds)
                ->where('direction', 'in')
                ->whereDate('created_at', $today)
                ->count();

            // 3. Antrean Tiket Prioritas untuk Agen:
            // Tampilkan tiket yang belum diambil agen (antrean baru) ATAU tiket yang sedang ia layani
            $antreanTiket = Ticket::whereIn('device_id', $deviceIds)
                ->where('status', 'open')
                ->where(function ($q) use ($user) {
                    $q->whereNull('agent_id')
                      ->orWhere('agent_id', $user->id);
                })
                ->with(['device', 'agent'])
                ->orderBy('has_new_message', 'desc')
                ->orderBy('updated_at', 'desc')
                ->take(8)
                ->get();

            // 4. Riwayat Tiket yang Berhasil Diselesaikan oleh Agen Ini
            $riwayatTiketSelesai = Ticket::where('agent_id', $user->id)
                ->whereIn('status', ['closed', 'resolved'])
                ->with('device')
                ->orderBy('updated_at', 'desc')
                ->take(5)
                ->get();

            return view('dashboard.agent', compact(
                'devices',
                'tiketBelumDiambil', 'tiketSayaAktif', 'totalBelumTerlayani',
                'tiketSayaSelesaiHariIni', 'tiketSayaSelesaiTotal',
                'pesanSayaHariIni', 'pesanMasukHariIni',
                'antreanTiket', 'riwayatTiketSelesai'
            ));
        }
    }
}
