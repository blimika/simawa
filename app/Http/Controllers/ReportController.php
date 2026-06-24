<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. DATA RINGKASAN UTAMA (SUMMARY CARDS)
        $summary = [
            'total_tickets'      => Ticket::count(),
            'open_tickets'       => Ticket::where('status', 'open')->count(),
            'closed_tickets'     => Ticket::whereIn('status', ['closed', 'resolved', 'selesai'])->count(),
            'unassigned_tickets' => Ticket::whereNull('agent_id')->count(),
        ];

        // 2. LAPORAN AGREGASI DETAIL PER AGEN
        // Menggunakan Raw Query Case When agar query berjalan super cepat (Single Query)
        $agentReports = User::select('users.id', 'users.name', 'users.email')
            ->leftJoin('tickets', 'users.id', '=', 'tickets.agent_id')
            ->selectRaw('COUNT(tickets.id) as total_handled')
            ->selectRaw("SUM(CASE WHEN tickets.status = 'open' THEN 1 ELSE 0 END) as open_count")
            ->selectRaw("SUM(CASE WHEN tickets.status IN ('closed', 'resolved', 'selesai') THEN 1 ELSE 0 END) as closed_count")
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('total_handled', 'desc')
            ->get();

       return view('reports.index', compact('summary', 'agentReports'));
    }
}
