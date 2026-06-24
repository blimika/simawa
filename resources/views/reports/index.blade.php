<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Performa & Tiket Agen') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 uppercase">Total Tiket Masuk</p>
                        <dd class="text-3xl font-bold text-gray-900 mt-1">{{ $summary['total_tickets'] }}</dd>
                    </div>
                    <div class="p-3 rounded-lg bg-indigo-50 text-indigo-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 uppercase">Tiket Berjalan (Open)</p>
                        <dd class="text-3xl font-bold text-amber-600 mt-1">{{ $summary['open_tickets'] }}</dd>
                    </div>
                    <div class="p-3 rounded-lg bg-amber-50 text-amber-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 uppercase">Tiket Selesai (Closed)</p>
                        <dd class="text-3xl font-bold text-emerald-600 mt-1">{{ $summary['closed_tickets'] }}</dd>
                    </div>
                    <div class="p-3 rounded-lg bg-emerald-50 text-emerald-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 uppercase">Unassigned (Antrean)</p>
                        <dd class="text-3xl font-bold text-red-600 mt-1">{{ $summary['unassigned_tickets'] }}</dd>
                    </div>
                    <div class="p-3 rounded-lg bg-red-50 text-red-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-white">
                    <h3 class="text-lg font-semibold text-gray-800">Tabel Rincian Performa Per Agen</h3>
                    <p class="text-xs text-gray-400 mt-1">Data akumulasi penanganan tiket live chat yang dihitung secara real-time.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Agen</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Handled</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Tiket Aktif (Open)</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Tiket Sukses (Closed)</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rasio Penyelesaian (CR)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($agentReports as $report)
                                @php
                                    // Hitung persentase penyelesaian tiket oleh agen
                                    $completionRate = $report->total_handled > 0
                                        ? round(($report->closed_count / $report->total_handled) * 100)
                                        : 0;
                                @endphp
                                <tr class="hover:bg-gray-50/70 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm uppercase">
                                                {{ substr($report->name, 0, 2) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-semibold text-gray-900">{{ $report->name }}</div>
                                                <div class="text-xs text-gray-400 font-mono">{{ $report->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-center text-gray-800">
                                        {{ $report->total_handled }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2.5 py-1 text-xs font-semibold text-amber-700 bg-amber-50 rounded-full border border-amber-100">
                                            {{ $report->open_count }} Tiket
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 rounded-full border border-emerald-100">
                                            {{ $report->closed_count }} Tiket
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap w-1/4">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-gray-700 w-10 text-right">{{ $completionRate }}%</span>
                                            <div class="w-full bg-gray-100 rounded-full h-2">
                                                <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: {{ $completionRate }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400 italic bg-gray-50">
                                        Belum ada data tiket yang terikat dengan Agen manapun saat ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
