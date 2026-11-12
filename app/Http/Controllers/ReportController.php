<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Tampilkan Laporan Performa & Tiket Agen dengan Filter Multi-Kriteria
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $tab = $request->input('tab', 'agents'); // 'agents', 'tickets', 'tags'

        // 1. Dapatkan Ruang Lingkup Device & Agen Berdasarkan Hak Akses Peran
        $devicesQuery = Device::query();
        $agentsQuery = User::where('role', 'agent')->withCount('handledDevices');

        if ($user->isSuperadmin()) {
            // Superadmin dapat melihat semua device dan semua agen
        } elseif ($user->isAdmin()) {
            $devicesQuery->where('user_id', $user->id);
            $agentsQuery->where('parent_id', $user->id);
        } else {
            // Agen melihat device yang ditugaskan kepadanya dan supervisor-nya
            $handled = $user->handledDevices()->pluck('devices.id');
            if ($handled->isNotEmpty()) {
                $devicesQuery->whereIn('id', $handled);
            } else {
                $devicesQuery->where('user_id', $user->parent_id);
            }
            $agentsQuery->where('id', $user->id);
        }

        $devices = $devicesQuery->orderBy('name', 'asc')->get();
        $allowedDeviceIds = $devices->pluck('id')->toArray();
        $agentsList = $agentsQuery->orderBy('name', 'asc')->get();
        $allowedAgentIds = $agentsList->pluck('id')->toArray();

        // 2. Filter Rentang Tanggal
        $dateRange = $request->input('date_range', 'all');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Helper untuk menerapkan filter tanggal ke query tickets
        $applyDateFilter = function ($q) use ($dateRange, $startDate, $endDate) {
            switch ($dateRange) {
                case 'today':
                    $q->whereDate('tickets.created_at', today());
                    break;
                case 'yesterday':
                    $q->whereDate('tickets.created_at', today()->subDay());
                    break;
                case '7days':
                    $q->where('tickets.created_at', '>=', now()->subDays(7));
                    break;
                case '30days':
                    $q->where('tickets.created_at', '>=', now()->subDays(30));
                    break;
                case 'this_month':
                    $q->whereYear('tickets.created_at', now()->year)
                      ->whereMonth('tickets.created_at', now()->month);
                    break;
                case 'last_month':
                    $lastMonth = now()->subMonth();
                    $q->whereYear('tickets.created_at', $lastMonth->year)
                      ->whereMonth('tickets.created_at', $lastMonth->month);
                    break;
                case 'custom':
                    if (!empty($startDate)) {
                        $q->whereDate('tickets.created_at', '>=', $startDate);
                    }
                    if (!empty($endDate)) {
                        $q->whereDate('tickets.created_at', '<=', $endDate);
                    }
                    break;
            }
        };

        // 3. Query Tiket Dasar Berdasarkan Filter yang Diterapkan
        $baseTicketsQuery = Ticket::query();
        if (!$user->isSuperadmin()) {
            $baseTicketsQuery->whereIn('device_id', $allowedDeviceIds);
        }

        if ($request->filled('device_id')) {
            $baseTicketsQuery->where('device_id', $request->device_id);
        }

        if ($request->filled('agent_id')) {
            if ($request->agent_id === 'unassigned') {
                $baseTicketsQuery->whereNull('agent_id');
            } else {
                $baseTicketsQuery->where('agent_id', $request->agent_id);
            }
        }

        if ($request->filled('status')) {
            if ($request->status === 'closed') {
                $baseTicketsQuery->whereIn('status', ['closed', 'resolved', 'selesai']);
            } elseif ($request->status === 'unassigned') {
                $baseTicketsQuery->where('status', 'open')->whereNull('agent_id');
            } else {
                $baseTicketsQuery->where('status', $request->status);
            }
        }

        if ($request->filled('tag')) {
            $baseTicketsQuery->where('tag', $request->tag);
        }

        // Terapkan filter tanggal ke query tiket dasar
        $applyDateFilter($baseTicketsQuery);

        // 4. Hitung Ringkasan Statistik (Summary Metrics)
        $totalTickets = (clone $baseTicketsQuery)->count();
        $openTickets = (clone $baseTicketsQuery)->where('status', 'open')->count();
        $closedTickets = (clone $baseTicketsQuery)->whereIn('status', ['closed', 'resolved', 'selesai'])->count();
        $unassignedTickets = (clone $baseTicketsQuery)->where('status', 'open')->whereNull('agent_id')->count();
        $completionRate = $totalTickets > 0 ? round(($closedTickets / $totalTickets) * 100, 1) : 0;
        $todayTickets = (clone $baseTicketsQuery)->whereDate('created_at', today())->count();

        $summary = [
            'total_tickets'       => $totalTickets,
            'open_tickets'        => $openTickets,
            'closed_tickets'      => $closedTickets,
            'unassigned_tickets'  => $unassignedTickets,
            'completion_rate'     => $completionRate,
            'today_tickets'       => $todayTickets,
        ];

        // 5. DATA PERFORMA PER AGEN (TAB 1)
        $allAgentReports = collect();
        if ($agentsList->isNotEmpty()) {
            // Bangun subquery agregasi tiket untuk agen
            $ticketSubquery = Ticket::query();
            if (!$user->isSuperadmin()) {
                $ticketSubquery->whereIn('device_id', $allowedDeviceIds);
            }
            if ($request->filled('device_id')) {
                $ticketSubquery->where('device_id', $request->device_id);
            }
            if ($request->filled('tag')) {
                $ticketSubquery->where('tag', $request->tag);
            }
            $applyDateFilter($ticketSubquery);

            $ticketStats = (clone $ticketSubquery)
                ->whereNotNull('agent_id')
                ->select(
                    'agent_id',
                    DB::raw('COUNT(*) as total_handled'),
                    DB::raw("SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_count"),
                    DB::raw("SUM(CASE WHEN status IN ('closed', 'resolved', 'selesai') THEN 1 ELSE 0 END) as closed_count")
                )
                ->groupBy('agent_id')
                ->get()
                ->keyBy('agent_id');

            // Susun data performa agen
            $allAgentReports = $agentsList->map(function ($agent) use ($ticketStats) {
                $stat = $ticketStats->get($agent->id);
                $handled = $stat ? (int) $stat->total_handled : 0;
                $open = $stat ? (int) $stat->open_count : 0;
                $closed = $stat ? (int) $stat->closed_count : 0;
                $rate = $handled > 0 ? round(($closed / $handled) * 100, 1) : 0;

                return (object) [
                    'id'              => $agent->id,
                    'name'            => $agent->name,
                    'username'        => $agent->username,
                    'email'           => $agent->email,
                    'is_active'       => $agent->is_active,
                    'devices_count'   => (int) ($agent->handled_devices_count ?? 0),
                    'total_handled'   => $handled,
                    'open_count'      => $open,
                    'closed_count'    => $closed,
                    'completion_rate' => $rate,
                ];
            })->sort(function ($a, $b) {
                if ($a->total_handled !== $b->total_handled) {
                    return $b->total_handled <=> $a->total_handled;
                }
                if ($a->completion_rate !== $b->completion_rate) {
                    return $b->completion_rate <=> $a->completion_rate;
                }
                return strcmp($a->name, $b->name);
            })->values()->map(function ($item, $idx) {
                $item->rank = $idx + 1;
                return $item;
            });
        }

        $totalAgentsCount = $allAgentReports->count();
        $topAgents = $allAgentReports->take(3);

        // Terapkan filter agen / pencarian pada daftar performa agen agar mudah menelusuri bila > 20 agen
        $filteredAgentReports = $allAgentReports;
        if ($request->filled('agent_id') && $request->agent_id !== 'unassigned') {
            $filteredAgentReports = $filteredAgentReports->where('id', (int) $request->agent_id)->values();
        }
        if ($tab === 'agents' && $request->filled('search')) {
            $agentSearch = trim($request->search);
            $filteredAgentReports = $filteredAgentReports->filter(function ($item) use ($agentSearch) {
                return stripos($item->name, $agentSearch) !== false
                    || stripos($item->username, $agentSearch) !== false
                    || stripos($item->email, $agentSearch) !== false;
            })->values();
        }

        $perPageAllowed = [10, 15, 25, 50];
        $perPage = (int) $request->input('per_page', 15);
        if (!in_array($perPage, $perPageAllowed, true)) {
            $perPage = 15;
        }

        $agentPage = max(1, (int) $request->input('agent_page', LengthAwarePaginator::resolveCurrentPage('agent_page')));
        $agentSlice = $filteredAgentReports->slice(($agentPage - 1) * $perPage, $perPage)->values();
        $agentReports = new LengthAwarePaginator(
            $agentSlice,
            $filteredAgentReports->count(),
            $perPage,
            $agentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'agent_page',
                'query' => $request->query(),
            ]
        );

        // 6. DATA RINCIAN TIKET (TAB 2)
        $tickets = collect();
        if ($tab === 'tickets') {
            $ticketsQuery = (clone $baseTicketsQuery)->with(['device', 'agent']);

            // Pencarian kata kunci nomor tiket / nomor WA / nama pelanggan
            if ($request->filled('search')) {
                $search = trim($request->search);
                $ticketsQuery->where(function ($q) use ($search) {
                    $q->where('ticket_number', 'like', "%{$search}%")
                      ->orWhere('number', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%");
                });
            }

            $tickets = $ticketsQuery->latest()->paginate(15)->withQueryString();
        }

        // 7. DATA DISTRIBUSI TAG & DEVICE (TAB 3)
        $tagDistribution = collect();
        $deviceDistribution = collect();
        if ($tab === 'tags') {
            // Distribusi Tag
            $tagQuery = (clone $baseTicketsQuery)
                ->select('tag', DB::raw('count(*) as count'))
                ->groupBy('tag')
                ->orderBy('count', 'desc')
                ->get();

            $tagDistribution = $tagQuery->map(function ($item) use ($totalTickets) {
                $tagName = !empty($item->tag) ? $item->tag : 'Tanpa Tag';
                $pct = $totalTickets > 0 ? round(($item->count / $totalTickets) * 100, 1) : 0;
                return (object) [
                    'name'       => $tagName,
                    'count'      => $item->count,
                    'percentage' => $pct,
                ];
            });

            // Distribusi Device WhatsApp
            $deviceQuery = (clone $baseTicketsQuery)
                ->with('device')
                ->select('device_id', DB::raw('count(*) as count'))
                ->groupBy('device_id')
                ->orderBy('count', 'desc')
                ->get();

            $deviceDistribution = $deviceQuery->map(function ($item) use ($totalTickets) {
                $devName = $item->device ? $item->device->name : 'Device #'.$item->device_id;
                $pct = $totalTickets > 0 ? round(($item->count / $totalTickets) * 100, 1) : 0;
                return (object) [
                    'device_id'  => $item->device_id,
                    'device_name'=> $devName,
                    'status'     => $item->device?->status ?? 'unknown',
                    'count'      => $item->count,
                    'percentage' => $pct,
                ];
            });
        }

        // Daftar Tag Unik untuk Dropdown Filter
        $availableTags = Ticket::query()
            ->when(!$user->isSuperadmin(), fn ($q) => $q->whereIn('device_id', $allowedDeviceIds))
            ->whereNotNull('tag')
            ->where('tag', '!=', '')
            ->distinct()
            ->pluck('tag');

        return view('reports.index', compact(
            'summary',
            'agentReports',
            'topAgents',
            'totalAgentsCount',
            'perPage',
            'tickets',
            'tagDistribution',
            'deviceDistribution',
            'devices',
            'agentsList',
            'availableTags',
            'tab'
        ));
    }

    /**
     * Export Laporan ke Berkas CSV
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        $type = $request->input('type', 'agents'); // 'agents' atau 'tickets'

        // 1. Terapkan Ruang Lingkup Device & Agen
        $devicesQuery = Device::query();
        $agentsQuery = User::where('role', 'agent');

        if ($user->isSuperadmin()) {
        } elseif ($user->isAdmin()) {
            $devicesQuery->where('user_id', $user->id);
            $agentsQuery->where('parent_id', $user->id);
        } else {
            $handled = $user->handledDevices()->pluck('devices.id');
            if ($handled->isNotEmpty()) {
                $devicesQuery->whereIn('id', $handled);
            } else {
                $devicesQuery->where('user_id', $user->parent_id);
            }
            $agentsQuery->where('id', $user->id);
        }

        $allowedDeviceIds = $devicesQuery->pluck('id')->toArray();
        $agentsList = $agentsQuery->orderBy('name', 'asc')->get();

        // 2. Filter Tanggal
        $dateRange = $request->input('date_range', 'all');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $applyDateFilter = function ($q) use ($dateRange, $startDate, $endDate) {
            switch ($dateRange) {
                case 'today':
                    $q->whereDate('tickets.created_at', today());
                    break;
                case 'yesterday':
                    $q->whereDate('tickets.created_at', today()->subDay());
                    break;
                case '7days':
                    $q->where('tickets.created_at', '>=', now()->subDays(7));
                    break;
                case '30days':
                    $q->where('tickets.created_at', '>=', now()->subDays(30));
                    break;
                case 'this_month':
                    $q->whereYear('tickets.created_at', now()->year)
                      ->whereMonth('tickets.created_at', now()->month);
                    break;
                case 'last_month':
                    $lastMonth = now()->subMonth();
                    $q->whereYear('tickets.created_at', $lastMonth->year)
                      ->whereMonth('tickets.created_at', $lastMonth->month);
                    break;
                case 'custom':
                    if (!empty($startDate)) $q->whereDate('tickets.created_at', '>=', $startDate);
                    if (!empty($endDate)) $q->whereDate('tickets.created_at', '<=', $endDate);
                    break;
            }
        };

        // EXPORT RIWAYAT TIKET (CSV)
        if ($type === 'tickets') {
            $filename = 'laporan_riwayat_tiket_' . date('Ymd_His') . '.csv';

            $ticketsQuery = Ticket::with(['device', 'agent']);
            if (!$user->isSuperadmin()) {
                $ticketsQuery->whereIn('device_id', $allowedDeviceIds);
            }
            if ($request->filled('device_id')) $ticketsQuery->where('device_id', $request->device_id);
            if ($request->filled('agent_id')) {
                if ($request->agent_id === 'unassigned') $ticketsQuery->whereNull('agent_id');
                else $ticketsQuery->where('agent_id', $request->agent_id);
            }
            if ($request->filled('status')) {
                if ($request->status === 'closed') $ticketsQuery->whereIn('status', ['closed', 'resolved', 'selesai']);
                elseif ($request->status === 'unassigned') $ticketsQuery->where('status', 'open')->whereNull('agent_id');
                else $ticketsQuery->where('status', $request->status);
            }
            if ($request->filled('tag')) $ticketsQuery->where('tag', $request->tag);
            $applyDateFilter($ticketsQuery);

            $headers = [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Pragma'              => 'no-cache',
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Expires'             => '0',
            ];

            return response()->stream(function () use ($ticketsQuery) {
                $handle = fopen('php://output', 'w');
                // Tulis BOM UTF-8 agar karakter rapi di Microsoft Excel
                fputs($handle, "\xEF\xBB\xBF");

                fputcsv($handle, [
                    'Nomor Tiket',
                    'Device WhatsApp',
                    'Nomor Pelanggan',
                    'Nama Pelanggan',
                    'Agen Penanggung Jawab',
                    'Tag / Kategori',
                    'Status Tiket',
                    'Waktu Dibuat',
                    'Waktu Terakhir Diperbarui'
                ]);

                $ticketsQuery->chunk(100, function ($chunk) use ($handle) {
                    foreach ($chunk as $t) {
                        fputcsv($handle, [
                            $t->ticket_number,
                            $t->device?->name ?? 'Device #'.$t->device_id,
                            $t->number,
                            $t->customer_name ?? '-',
                            $t->agent?->name ?? 'Belum Diambil',
                            $t->tag ? ucfirst($t->tag) : 'Umum',
                            strtoupper($t->status),
                            $t->created_at->format('Y-m-d H:i:s'),
                            $t->updated_at->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

                fclose($handle);
            }, 200, $headers);
        }

        // EXPORT PERFORMA AGEN (CSV)
        $filename = 'laporan_performa_agen_' . date('Ymd_His') . '.csv';

        $ticketSubquery = Ticket::query();
        if (!$user->isSuperadmin()) {
            $ticketSubquery->whereIn('device_id', $allowedDeviceIds);
        }
        if ($request->filled('device_id')) $ticketSubquery->where('device_id', $request->device_id);
        if ($request->filled('tag')) $ticketSubquery->where('tag', $request->tag);
        $applyDateFilter($ticketSubquery);

        $ticketStats = (clone $ticketSubquery)
            ->whereNotNull('agent_id')
            ->select(
                'agent_id',
                DB::raw('COUNT(*) as total_handled'),
                DB::raw("SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_count"),
                DB::raw("SUM(CASE WHEN status IN ('closed', 'resolved', 'selesai') THEN 1 ELSE 0 END) as closed_count")
            )
            ->groupBy('agent_id')
            ->get()
            ->keyBy('agent_id');

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($agentsList, $ticketStats) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Peringkat',
                'Nama Agen',
                'Username',
                'Email',
                'Status Akun',
                'Total Tiket Ditangani',
                'Tiket Aktif (Open)',
                'Tiket Selesai (Closed)',
                'Rasio Penyelesaian (%)'
            ]);

            $rank = 1;
            $sortedAgents = $agentsList->map(function ($agent) use ($ticketStats) {
                $stat = $ticketStats->get($agent->id);
                $handled = $stat ? (int) $stat->total_handled : 0;
                $open = $stat ? (int) $stat->open_count : 0;
                $closed = $stat ? (int) $stat->closed_count : 0;
                $rate = $handled > 0 ? round(($closed / $handled) * 100, 1) : 0;

                return [
                    'agent'   => $agent,
                    'handled' => $handled,
                    'open'    => $open,
                    'closed'  => $closed,
                    'rate'    => $rate,
                ];
            })->sortByDesc('handled')->values();

            foreach ($sortedAgents as $item) {
                $agent = $item['agent'];
                fputcsv($handle, [
                    $rank++,
                    $agent->name,
                    $agent->username,
                    $agent->email,
                    $agent->is_active ? 'Aktif' : 'Non-Aktif',
                    $item['handled'],
                    $item['open'],
                    $item['closed'],
                    $item['rate'] . '%',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Dapatkan Detail Performa dan Riwayat Tiket Seorang Agen (JSON untuk Modal Interaktif)
     */
    public function agentDetail(User $agent, Request $request)
    {
        $user = auth()->user();

        // Validasi Otorisasi Akses
        if (!$user->isSuperadmin()) {
            if ($user->isAdmin() && $agent->parent_id !== $user->id) {
                abort(403, 'Akses ditolak.');
            }
            if ($user->isAgent() && $agent->id !== $user->id) {
                abort(403, 'Akses ditolak.');
            }
        }

        $ticketsQuery = Ticket::with('device')->where('agent_id', $agent->id);

        $totalHandled = (clone $ticketsQuery)->count();
        $openCount = (clone $ticketsQuery)->where('status', 'open')->count();
        $closedCount = (clone $ticketsQuery)->whereIn('status', ['closed', 'resolved', 'selesai'])->count();
        $rate = $totalHandled > 0 ? round(($closedCount / $totalHandled) * 100, 1) : 0;

        $tagStats = (clone $ticketsQuery)
            ->select('tag', DB::raw('count(*) as count'))
            ->groupBy('tag')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) use ($totalHandled) {
                return [
                    'tag'        => $item->tag ?: 'Umum',
                    'count'      => $item->count,
                    'percentage' => $totalHandled > 0 ? round(($item->count / $totalHandled) * 100, 1) : 0,
                ];
            });

        $recentTickets = (clone $ticketsQuery)
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($t) {
                return [
                    'id'            => $t->id,
                    'ticket_number' => $t->ticket_number,
                    'device_name'   => $t->device?->name ?? 'Device #'.$t->device_id,
                    'customer_name' => $t->customer_name ?: 'Pelanggan',
                    'number'        => $t->number,
                    'status'        => $t->status,
                    'tag'           => $t->tag ? ucfirst($t->tag) : 'Umum',
                    'created_at'    => $t->created_at->format('d M Y H:i'),
                    'updated_at'    => $t->updated_at->diffForHumans(),
                ];
            });

        return response()->json([
            'agent' => [
                'id'            => $agent->id,
                'name'          => $agent->name,
                'username'      => $agent->username,
                'email'         => $agent->email,
                'is_active'     => $agent->is_active,
                'initials'      => strtoupper(substr($agent->name, 0, 2)),
                'devices_count' => $agent->handledDevices()->count(),
            ],
            'metrics' => [
                'total_handled'   => $totalHandled,
                'open_count'      => $openCount,
                'closed_count'    => $closedCount,
                'completion_rate' => $rate,
            ],
            'tags'           => $tagStats,
            'recent_tickets' => $recentTickets,
        ]);
    }
}
