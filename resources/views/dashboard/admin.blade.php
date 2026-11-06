<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    {{ __('Dashboard Admin SIMAWA') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Selamat datang, <span class="font-semibold text-gray-800">{{ auth()->user()->name }}</span>! Pantau performa pelayanan tiket, beban kerja agen, dan status device WhatsApp.
                </p>
            </div>
            <!-- Shortcut Aksi Cepat Header -->
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('messages.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <span>Live Chat</span>
                    @if($tiketBelumDiambil > 0)
                        <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white bg-rose-500 rounded-full animate-pulse">
                            {{ $tiketBelumDiambil }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('broadcast.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                    </svg>
                    <span>Broadcast</span>
                </a>
                <a href="{{ route('devices.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 rounded-xl text-xs font-semibold shadow-sm transition">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Device</span>
                </a>
                <a href="{{ route('agents.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 rounded-xl text-xs font-semibold shadow-sm transition">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span>Agen</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- 6 KARTU STATISTIK OPERASIONAL CS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                
                <!-- 1. Status Device WhatsApp -->
                <div class="bg-white rounded-2xl p-4 border border-gray-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        @if($connectedDevices === $totalDevice && $totalDevice > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span>
                                Semua Online
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800">
                                {{ $connectedDevices }}/{{ $totalDevice }} Online
                            </span>
                        @endif
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ $connectedDevices }} <span class="text-xs font-normal text-gray-400">/ {{ $totalDevice }} device</span></div>
                        <div class="text-xs font-semibold text-gray-700 mt-0.5">Device WhatsApp</div>
                    </div>
                    <div class="mt-3 pt-2 border-t border-gray-100 text-[11px]">
                        <a href="{{ route('devices.index') }}" class="text-teal-600 hover:text-teal-700 font-semibold inline-flex items-center gap-1">
                            Kelola Device &rarr;
                        </a>
                    </div>
                </div>

                <!-- 2. Tiket Belum Terlayani (Waiting Queue) -->
                <div class="bg-white rounded-2xl p-4 border border-amber-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        @if($tiketBelumDiambil > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700 animate-pulse">
                                Menunggu
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-600">
                                Kosong
                            </span>
                        @endif
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-extrabold text-amber-600 tracking-tight">{{ $tiketBelumDiambil }}</div>
                        <div class="text-xs font-semibold text-gray-700 mt-0.5">Belum Terlayani</div>
                        <p class="text-[10px] text-gray-400 mt-0.5">Total {{ $totalTiketOpen }} tiket open</p>
                    </div>
                    <div class="mt-3 pt-2 border-t border-gray-100 text-[11px]">
                        <a href="{{ route('messages.index') }}" class="text-amber-600 hover:text-amber-700 font-semibold inline-flex items-center gap-1">
                            Buka Antrean &rarr;
                        </a>
                    </div>
                </div>

                <!-- 3. Tiket Sedang Dilayani -->
                <div class="bg-white rounded-2xl p-4 border border-blue-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-800">
                            In-Service
                        </span>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-extrabold text-blue-600 tracking-tight">{{ $tiketSedangDilayani }}</div>
                        <div class="text-xs font-semibold text-gray-700 mt-0.5">Sedang Dilayani</div>
                        <p class="text-[10px] text-gray-400 mt-0.5">Ditangani oleh agen</p>
                    </div>
                    <div class="mt-3 pt-2 border-t border-gray-100 text-[11px]">
                        <span class="text-blue-700 font-medium">Dalam Proses</span>
                    </div>
                </div>

                <!-- 4. Tiket Selesai / Terlayani Hari Ini -->
                <div class="bg-white rounded-2xl p-4 border border-emerald-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                            Hari Ini
                        </span>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-extrabold text-emerald-600 tracking-tight">{{ $tiketSelesaiHariIni }}</div>
                        <div class="text-xs font-semibold text-gray-700 mt-0.5">Terlayani Hari Ini</div>
                        <p class="text-[10px] text-gray-400 mt-0.5">Total {{ $tiketSelesaiTotal }} tiket tuntas</p>
                    </div>
                    <div class="mt-3 pt-2 border-t border-gray-100 text-[11px]">
                        <span class="text-emerald-700 font-semibold">Tuntas</span>
                    </div>
                </div>

                <!-- 5. Trafik Pesan Hari Ini -->
                <div class="bg-white rounded-2xl p-4 border border-purple-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-100 text-purple-800">
                            Trafik
                        </span>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-extrabold text-purple-600 tracking-tight">{{ $pesanHariIni }}</div>
                        <div class="text-xs font-semibold text-gray-700 mt-0.5">Pesan Hari Ini</div>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $pesanMasukHariIni }} in &bull; {{ $pesanKeluarHariIni }} out</p>
                    </div>
                    <div class="mt-3 pt-2 border-t border-gray-100 text-[11px]">
                        <span class="text-purple-700 font-medium">Interaksi Chat</span>
                    </div>
                </div>

                <!-- 6. Total Agen Customer Service -->
                <div class="bg-white rounded-2xl p-4 border border-indigo-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-100 text-indigo-800">
                            Tim CS
                        </span>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-extrabold text-indigo-600 tracking-tight">{{ $totalAgent }}</div>
                        <div class="text-xs font-semibold text-gray-700 mt-0.5">Total Agen</div>
                        <p class="text-[10px] text-gray-400 mt-0.5">Staf pelayanan CS</p>
                    </div>
                    <div class="mt-3 pt-2 border-t border-gray-100 text-[11px]">
                        <a href="{{ route('agents.index') }}" class="text-indigo-600 hover:text-indigo-700 font-semibold inline-flex items-center gap-1">
                            Kelola Agen &rarr;
                        </a>
                    </div>
                </div>

            </div>

            <!-- TABEL MONITORING BEBAN KERJA & KINERJA AGEN -->
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Monitoring Kinerja & Beban Kerja Agen</span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Pantau jumlah tiket yang sedang ditangani dan telah diselesaikan oleh masing-masing agen
                        </p>
                    </div>
                    <a href="{{ route('agents.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 inline-flex items-center gap-1 self-start sm:self-auto">
                        <span>Kelola Manajemen Agen</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50/75 text-gray-500 uppercase tracking-wider font-semibold border-b border-gray-100">
                            <tr>
                                <th class="py-3 px-4">Nama Agen</th>
                                <th class="py-3 px-4">Akses Device</th>
                                <th class="py-3 px-4 text-center">Sedang Dilayani (Open)</th>
                                <th class="py-3 px-4 text-center">Selesai Hari Ini</th>
                                <th class="py-3 px-4 text-center">Total Terlayani</th>
                                <th class="py-3 px-4 text-center">Pesan Dibalas Hari Ini</th>
                                <th class="py-3 px-4 text-center">Status Akun</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @forelse($agentsPerformance as $item)
                                @php
                                    $ag = $item['agent'];
                                    $agInitials = strtoupper(substr(trim($ag->name), 0, 2));
                                @endphp
                                <tr class="hover:bg-gray-50/70 transition">
                                    <td class="py-3.5 px-4 font-medium text-gray-900">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                                {{ $agInitials }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900">{{ $ag->name }}</div>
                                                <div class="text-[11px] text-gray-400 font-mono">{{ $ag->email }} &bull; UID: {{ $ag->user_uid }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @forelse($ag->handledDevices as $hd)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                                    {{ $hd->name }}
                                                </span>
                                            @empty
                                                <span class="text-[11px] text-gray-400 italic">Semua Device</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if($item['open_count'] > 0)
                                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                                {{ $item['open_count'] }} aktif
                                            </span>
                                        @else
                                            <span class="text-gray-400 font-semibold">0</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-bold text-emerald-600">
                                        {{ $item['closed_today'] }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-bold text-gray-900">
                                        {{ $item['closed_count'] }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-semibold text-purple-700">
                                        {{ $item['messages_today'] }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        @if($ag->is_active ?? true)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1"></span>
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-600">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400">
                                        Belum ada agen yang terdaftar. <a href="{{ route('agents.create') }}" class="text-indigo-600 hover:underline font-semibold">Tambah Agen Sekarang</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- DUA KOLOM: ANTREAN TIKET TERKINI & STATUS PERANGKAT -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- KOLOM KIRI (2 COLS): ANTREAN TIKET TERKINI -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                        <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <span>Antrean Tiket Terkini (Butuh Penanganan)</span>
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Daftar tiket aktif yang sedang berlangsung atau belum diambil agen
                                </p>
                            </div>
                            <a href="{{ route('messages.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 self-start sm:self-auto">
                                <span>Buka Live Chat</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @forelse($antreanTiket as $ticket)
                                @php
                                    $initials = strtoupper(substr(trim($ticket->customer_name ?: ($ticket->number ?: 'P')), 0, 2));
                                    $isUnassigned = is_null($ticket->agent_id);
                                @endphp
                                <div class="p-4 hover:bg-gray-50/80 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="relative shrink-0">
                                            @if(!empty($ticket->avatar_url))
                                                <img src="{{ $ticket->avatar_url }}" alt="{{ $ticket->customer_name }}" class="w-10 h-10 rounded-full object-cover ring-2 ring-gray-100 shadow-sm" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                                                <div class="hidden w-10 h-10 rounded-full bg-gradient-to-br from-emerald-600 to-teal-700 text-white font-bold text-xs flex items-center justify-center ring-2 ring-gray-100 shadow-sm">
                                                    {{ $initials }}
                                                </div>
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-600 to-teal-700 text-white font-bold text-xs flex items-center justify-center ring-2 ring-gray-100 shadow-sm">
                                                    {{ $initials }}
                                                </div>
                                            @endif
                                            @if($ticket->has_new_message)
                                                <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-rose-500 border-2 border-white rounded-full animate-ping"></span>
                                                <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-rose-500 border-2 border-white rounded-full"></span>
                                            @endif
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h4 class="text-sm font-bold text-gray-900 truncate">
                                                    {{ $ticket->customer_name ?: 'Pelanggan WhatsApp' }}
                                                </h4>
                                                <span class="text-xs text-gray-400 font-mono">
                                                    ({{ $ticket->number }})
                                                </span>
                                                @if($ticket->has_new_message)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">
                                                        Pesan Baru
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2 text-xs text-gray-500 mt-0.5 flex-wrap">
                                                <span class="font-mono text-gray-600">{{ $ticket->ticket_number }}</span>
                                                <span>&bull;</span>
                                                <span>Device: <strong class="text-gray-700 font-medium">{{ $ticket->device->name ?? 'WhatsApp' }}</strong></span>
                                                <span>&bull;</span>
                                                <span>{{ $ticket->updated_at ? $ticket->updated_at->diffForHumans() : '-' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 shrink-0 self-end sm:self-auto">
                                        @if($isUnassigned)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>
                                                Belum Terlayani
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                                Agen: {{ $ticket->agent->name ?? 'Operator' }}
                                            </span>
                                        @endif
                                        <a href="{{ route('messages.index', ['ticket_id' => $ticket->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                                            <span>Lihat Chat</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-xs text-gray-400">
                                    Tidak ada tiket terbuka saat ini. Semua pesan telah terlayani!
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN (1 COL): STATUS DEVICE & AKSI CEPAT -->
                <div class="space-y-6">

                    <!-- STATUS DEVICE WHATSAPP -->
                    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span>Status Device WhatsApp</span>
                            </h3>
                            <a href="{{ route('devices.index') }}" class="text-xs text-indigo-600 hover:underline font-semibold">
                                Kelola
                            </a>
                        </div>

                        <div class="space-y-3">
                            @forelse($devices as $dev)
                                @php
                                    $isConnected = ($dev->status === 'connected');
                                @endphp
                                <div class="p-3 rounded-xl border {{ $isConnected ? 'border-emerald-100 bg-emerald-50/40' : 'border-rose-100 bg-rose-50/40' }} flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-8 h-8 rounded-lg {{ $isConnected ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }} flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="text-xs font-bold text-gray-900 truncate">{{ $dev->name }}</h4>
                                            <p class="text-[11px] text-gray-500 font-mono truncate">{{ $dev->phone_number ?: 'Nomor belum sinkron' }}</p>
                                        </div>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        @if($isConnected)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span>
                                                Connected
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800">
                                                Disconnected
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-xs text-gray-400">
                                    Belum ada device. <a href="{{ route('devices.create') }}" class="text-teal-600 font-semibold hover:underline">Tambah Device</a>.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- TIKET TERAKHIR SELESAI -->
                    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Terakhir Diselesaikan</span>
                            </h3>
                            <span class="text-xs text-emerald-600 font-semibold">
                                Total: {{ $tiketSelesaiTotal }}
                            </span>
                        </div>

                        <div class="space-y-2 text-xs">
                            @forelse($tiketSelesaiTerbaru as $closed)
                                <div class="p-2.5 rounded-xl bg-gray-50/80 border border-gray-100 flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="font-bold text-gray-900 truncate">
                                            {{ $closed->customer_name ?: $closed->number }}
                                        </div>
                                        <div class="text-[11px] text-gray-400 font-mono mt-0.5">
                                            {{ $closed->ticket_number }} &bull; Oleh: {{ $closed->agent->name ?? 'Admin' }}
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 shrink-0">
                                        Selesai
                                    </span>
                                </div>
                            @empty
                                <div class="text-center py-4 text-xs text-gray-400">
                                    Belum ada tiket yang diselesaikan.
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
