<x-app-layout>
    @php
        $tagColors = [
            'umum' => 'bg-slate-100 text-slate-700 border-slate-300',
            'pengaduan' => 'bg-rose-100 text-rose-700 border-rose-300',
            'permintaan data' => 'bg-sky-100 text-sky-700 border-sky-300',
            'promosi' => 'bg-amber-100 text-amber-700 border-amber-300',
            'rekrutmen' => 'bg-fuchsia-100 text-fuchsia-700 border-fuchsia-300',
            'konsultasi' => 'bg-emerald-100 text-emerald-700 border-emerald-300',
        ];
    @endphp

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                        {{ __('Laporan Performa & Tiket Agen') }}
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 border border-indigo-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mr-1.5 animate-pulse"></span>
                        Real-time Analytics
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    Pantau produktivitas penanganan tiket live chat, rasio penyelesaian agen, dan distribusi layanan secara realtime.
                </p>
            </div>

            {{-- Tombol Aksi Header --}}
            <div class="flex flex-wrap items-center gap-2.5 print:hidden" x-data="{ exportOpen: false }">
                <a href="{{ route('reports.index') }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 text-gray-700 font-semibold py-2 px-3.5 rounded-lg text-sm border border-gray-300 transition shadow-sm" title="Muat Ulang Halaman">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 12H16"></path></svg>
                    Refresh
                </a>

                <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 text-gray-700 font-semibold py-2 px-3.5 rounded-lg text-sm border border-gray-300 transition shadow-sm" title="Cetak Laporan">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak
                </button>

                {{-- Dropdown Export CSV --}}
                <div class="relative">
                    <button type="button" @click="exportOpen = !exportOpen" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Export CSV
                        <svg class="w-3 h-3 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <div x-show="exportOpen" 
                         @click.outside="exportOpen = false" 
                         x-cloak 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 py-1 z-50 border border-gray-100">
                        <a href="{{ route('reports.export', array_merge(request()->query(), ['type' => 'agents'])) }}" 
                           class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition">
                            <span class="text-base">👥</span>
                            <div>
                                <div class="font-bold">Export Performa Agen</div>
                                <div class="text-[10px] text-gray-400">Ringkasan ranking &amp; penanganan</div>
                            </div>
                        </a>
                        <div class="border-t border-gray-100 my-1"></div>
                        <a href="{{ route('reports.export', array_merge(request()->query(), ['type' => 'tickets'])) }}" 
                           class="flex items-center gap-2.5 px-4 py-2.5 text-xs text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 transition">
                            <span class="text-base">🎫</span>
                            <div>
                                <div class="font-bold">Export Riwayat Tiket</div>
                                <div class="text-[10px] text-gray-400">Rincian seluruh data tiket</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen" x-data="reportManager()">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- 1. KARTU STATISTIK UTAMA (5 CARDS) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                {{-- Total Tiket Masuk --}}
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Tiket Masuk</p>
                        <div class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($summary['total_tickets']) }}</div>
                        <div class="text-[11px] text-gray-500 mt-0.5">Hari ini: <b class="text-indigo-600">+{{ $summary['today_tickets'] }}</b></div>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                </div>

                {{-- Tiket Aktif (Open) --}}
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tiket Berjalan</p>
                        <div class="text-2xl font-bold text-amber-600 mt-1">{{ number_format($summary['open_tickets']) }}</div>
                        <div class="text-[11px] text-amber-600 mt-0.5 font-medium">Sedang dilayani agen</div>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                {{-- Tiket Selesai (Closed) --}}
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tiket Selesai</p>
                        <div class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($summary['closed_tickets']) }}</div>
                        <div class="text-[11px] text-emerald-600 mt-0.5 font-medium">Berhasil ditutup</div>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>

                {{-- Unassigned (Antrean) --}}
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Belum Diambil</p>
                        <div class="text-2xl font-bold text-rose-600 mt-1">{{ number_format($summary['unassigned_tickets']) }}</div>
                        <div class="text-[11px] text-rose-600 mt-0.5 font-medium">Menunggu di antrean</div>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                </div>

                {{-- Rasio Penyelesaian (CR) --}}
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                    <div class="w-full">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Rasio Selesai (CR)</p>
                            <span class="text-xs font-bold text-teal-600">{{ $summary['completion_rate'] }}%</span>
                        </div>
                        <div class="text-2xl font-bold text-teal-700 mt-1">{{ $summary['completion_rate'] }}%</div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2">
                            <div class="bg-teal-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ min($summary['completion_rate'], 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. BAR FILTER MULTI-KRITERIA (RESPONSIF) --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 print:hidden" x-data="{ customDate: '{{ request('date_range') === 'custom' ? 'true' : 'false' }}' }">
                <form method="GET" action="{{ route('reports.index') }}" class="space-y-4">
                    {{-- Simpan Tab Aktif saat melakukan filter --}}
                    <input type="hidden" name="tab" value="{{ $tab }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
                        {{-- Filter Rentang Waktu --}}
                        <div class="lg:col-span-3">
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Rentang Tanggal</label>
                            <select name="date_range" @change="customDate = ($event.target.value === 'custom')" class="w-full py-2 px-3 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                                <option value="all" {{ request('date_range') === 'all' || !request('date_range') ? 'selected' : '' }}>Semua Waktu</option>
                                <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Hari Ini</option>
                                <option value="yesterday" {{ request('date_range') === 'yesterday' ? 'selected' : '' }}>Kemarin</option>
                                <option value="7days" {{ request('date_range') === '7days' ? 'selected' : '' }}>7 Hari Terakhir</option>
                                <option value="30days" {{ request('date_range') === '30days' ? 'selected' : '' }}>30 Hari Terakhir</option>
                                <option value="this_month" {{ request('date_range') === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                                <option value="last_month" {{ request('date_range') === 'last_month' ? 'selected' : '' }}>Bulan Lalu</option>
                                <option value="custom" {{ request('date_range') === 'custom' ? 'selected' : '' }}>Kustom Rentang Tanggal</option>
                            </select>
                        </div>

                        {{-- Filter Device --}}
                        <div class="lg:col-span-3">
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Device WhatsApp</label>
                            <select name="device_id" class="w-full py-2 px-3 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                                <option value="">Semua Device ({{ $devices->count() }})</option>
                                @foreach($devices as $dev)
                                    <option value="{{ $dev->id }}" {{ request('device_id') == $dev->id ? 'selected' : '' }}>
                                        {{ $dev->name }} ({{ $dev->status === 'connected' ? '🟢 Online' : '🔴 Offline' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter Agen --}}
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Agen Petugas</label>
                            <select name="agent_id" class="w-full py-2 px-3 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                                <option value="">Semua Agen</option>
                                <option value="unassigned" {{ request('agent_id') === 'unassigned' ? 'selected' : '' }}>-- Belum Diambil --</option>
                                @foreach($agentsList as $ag)
                                    <option value="{{ $ag->id }}" {{ request('agent_id') == $ag->id ? 'selected' : '' }}>
                                        {{ $ag->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter Tag Layanan --}}
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Tag Layanan</label>
                            <select name="tag" class="w-full py-2 px-3 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                                <option value="">Semua Tag</option>
                                @foreach($availableTags as $avTag)
                                    <option value="{{ $avTag }}" {{ request('tag') == $avTag ? 'selected' : '' }}>
                                        Tag: {{ ucfirst($avTag) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Filter Status Tiket --}}
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Status Tiket</label>
                            <select name="status" class="w-full py-2 px-3 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                                <option value="">Semua Status</option>
                                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Tiket Open</option>
                                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Tiket Closed</option>
                                <option value="unassigned" {{ request('status') === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                            </select>
                        </div>
                    </div>

                    {{-- Baris Tambahan: Tanggal Kustom & Pencarian --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 pt-1 border-t border-gray-100">
                        {{-- Input Tanggal Kustom Mulai --}}
                        <div class="lg:col-span-3" x-show="customDate" x-cloak>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Tanggal Mulai</label>
                            <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full py-2 px-3 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        </div>

                        {{-- Input Tanggal Kustom Selesai --}}
                        <div class="lg:col-span-3" x-show="customDate" x-cloak>
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Tanggal Sampai</label>
                            <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full py-2 px-3 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        </div>

                        {{-- Pencarian Kata Kunci --}}
                        <div :class="customDate ? 'lg:col-span-4' : 'lg:col-span-10'">
                            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Cari Nomor Tiket / Pelanggan</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </span>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Contoh: LYN-260518-..., 62812..., Budi" class="w-full pl-9 pr-3 py-2 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                            </div>
                        </div>

                        {{-- Tombol Submit Filter --}}
                        <div class="lg:col-span-2 flex items-end gap-2">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-3 rounded-lg text-sm transition shadow-sm flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                                <span>Terapkan</span>
                            </button>
                            @if(request()->anyFilled(['search', 'device_id', 'agent_id', 'tag', 'status', 'start_date', 'end_date']) || (request('date_range') && request('date_range') !== 'all'))
                                <a href="{{ route('reports.index', ['tab' => $tab]) }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded-lg text-sm transition border border-gray-300" title="Reset Filter">
                                    &times;
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            {{-- 3. TAB NAVIGASI LAPORAN (PERFORMA AGEN / RIWAYAT TIKET / DISTRIBUSI TAG) --}}
            <div class="flex items-center gap-2 border-b border-gray-200 print:hidden">
                <a href="{{ route('reports.index', array_merge(request()->query(), ['tab' => 'agents'])) }}" 
                   class="pb-3 px-4 text-sm font-bold border-b-2 transition flex items-center gap-2 {{ $tab === 'agents' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <span>👥 Performa Agen</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $tab === 'agents' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $agentReports->count() }} Agen
                    </span>
                </a>

                <a href="{{ route('reports.index', array_merge(request()->query(), ['tab' => 'tickets'])) }}" 
                   class="pb-3 px-4 text-sm font-bold border-b-2 transition flex items-center gap-2 {{ $tab === 'tickets' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <span>🎫 Riwayat Tiket Terperinci</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $tab === 'tickets' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $summary['total_tickets'] }}
                    </span>
                </a>

                <a href="{{ route('reports.index', array_merge(request()->query(), ['tab' => 'tags'])) }}" 
                   class="pb-3 px-4 text-sm font-bold border-b-2 transition flex items-center gap-2 {{ $tab === 'tags' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    <span>🏷️ Distribusi Tag &amp; Device</span>
                </a>
            </div>

            {{-- ========================================================== --}}
            {{-- KONTEN TAB 1: LAPORAN PERFORMA PER AGEN                    --}}
            {{-- ========================================================== --}}
            @if($tab === 'agents')
                {{-- Highlight Leaderboard Top 3 Agen --}}
                @if($agentReports->isNotEmpty() && $agentReports->first()->total_handled > 0)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($agentReports->take(3) as $index => $topAgent)
                            @if($topAgent->total_handled > 0)
                                <div class="bg-white rounded-xl p-5 border shadow-sm relative overflow-hidden {{ $index === 0 ? 'border-amber-200 bg-gradient-to-br from-amber-50/40 via-white to-white' : 'border-gray-100' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm uppercase shadow-sm
                                                {{ $index === 0 ? 'bg-amber-100 text-amber-800 border-2 border-amber-300' : ($index === 1 ? 'bg-slate-200 text-slate-800 border-2 border-slate-300' : 'bg-orange-100 text-orange-800 border-2 border-orange-300') }}">
                                                {{ substr($topAgent->name, 0, 2) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 text-sm flex items-center gap-1.5">
                                                    <span>{{ $topAgent->name }}</span>
                                                    @if($index === 0)
                                                        <span title="Juara 1 Penanganan Tiket">👑</span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-400 font-mono">{{ $topAgent->username }}</div>
                                            </div>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $index === 0 ? 'bg-amber-100 text-amber-800' : ($index === 1 ? 'bg-slate-100 text-slate-700' : 'bg-orange-100 text-orange-800') }}">
                                            Peringkat #{{ $index + 1 }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-gray-100 text-center">
                                        <div>
                                            <div class="text-[10px] text-gray-400 uppercase font-semibold">Total Tiket</div>
                                            <div class="text-base font-extrabold text-gray-800">{{ $topAgent->total_handled }}</div>
                                        </div>
                                        <div>
                                            <div class="text-[10px] text-gray-400 uppercase font-semibold">Selesai</div>
                                            <div class="text-base font-extrabold text-emerald-600">{{ $topAgent->closed_count }}</div>
                                        </div>
                                        <div>
                                            <div class="text-[10px] text-gray-400 uppercase font-semibold">Rasio (CR)</div>
                                            <div class="text-base font-extrabold text-indigo-600">{{ $topAgent->completion_rate }}%</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                {{-- Tabel Performa Agen Lengkap --}}
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-base font-bold text-gray-800">Tabel Rincian Performa &amp; Produktivitas Agen</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Akumulasi tiket yang dihitung berdasarkan filter rentang tanggal dan device aktif.</p>
                        </div>
                        <div class="text-xs text-gray-400">
                            Total: <b>{{ $agentReports->count() }}</b> Agen Terdaftar
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/80 text-gray-600 uppercase text-xs font-semibold tracking-wider border-b border-gray-200">
                                    <th class="py-3.5 px-6 text-center w-14">Rank</th>
                                    <th class="py-3.5 px-6">Agen Petugas</th>
                                    <th class="py-3.5 px-6 text-center">Status Akun</th>
                                    <th class="py-3.5 px-6 text-center">Device Handled</th>
                                    <th class="py-3.5 px-6 text-center">Total Handled</th>
                                    <th class="py-3.5 px-6 text-center">Tiket Open</th>
                                    <th class="py-3.5 px-6 text-center">Tiket Closed</th>
                                    <th class="py-3.5 px-6 text-left">Rasio Penyelesaian (CR)</th>
                                    <th class="py-3.5 px-6 text-center print:hidden">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 text-sm divide-y divide-gray-100">
                                @forelse($agentReports as $index => $report)
                                    <tr class="hover:bg-gray-50/70 transition">
                                        {{-- Rank --}}
                                        <td class="py-4 px-6 whitespace-nowrap text-center font-bold text-xs text-gray-400">
                                            @if($index === 0 && $report->total_handled > 0)
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-800 font-extrabold text-xs shadow-xs">1</span>
                                            @elseif($index === 1 && $report->total_handled > 0)
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 text-slate-800 font-extrabold text-xs shadow-xs">2</span>
                                            @elseif($index === 2 && $report->total_handled > 0)
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-orange-100 text-orange-800 font-extrabold text-xs shadow-xs">3</span>
                                            @else
                                                #{{ $index + 1 }}
                                            @endif
                                        </td>

                                        {{-- Nama Agen --}}
                                        <td class="py-4 px-6 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                                                    {{ substr($report->name, 0, 2) }}
                                                </div>
                                                <div>
                                                    <div class="text-sm font-bold text-gray-900">{{ $report->name }}</div>
                                                    <div class="text-xs text-gray-400 font-mono">{{ $report->email }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Status Akun --}}
                                        <td class="py-4 px-6 whitespace-nowrap text-center">
                                            @if($report->is_active)
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Device Handled --}}
                                        <td class="py-4 px-6 whitespace-nowrap text-center text-xs font-semibold text-gray-700">
                                            {{ $report->devices_count }} Device
                                        </td>

                                        {{-- Total Handled --}}
                                        <td class="py-4 px-6 whitespace-nowrap text-sm font-extrabold text-center text-gray-900">
                                            {{ number_format($report->total_handled) }}
                                        </td>

                                        {{-- Tiket Open --}}
                                        <td class="py-4 px-6 whitespace-nowrap text-center">
                                            <span class="px-2.5 py-1 text-xs font-semibold text-amber-700 bg-amber-50 rounded-full border border-amber-200">
                                                {{ $report->open_count }} Tiket
                                            </span>
                                        </td>

                                        {{-- Tiket Closed --}}
                                        <td class="py-4 px-6 whitespace-nowrap text-center">
                                            <span class="px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 rounded-full border border-emerald-200">
                                                {{ $report->closed_count }} Tiket
                                            </span>
                                        </td>

                                        {{-- Rasio Penyelesaian (CR) --}}
                                        <td class="py-4 px-6 whitespace-nowrap w-48">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-bold text-gray-700 w-11 text-right">{{ $report->completion_rate }}%</span>
                                                <div class="w-full bg-gray-100 rounded-full h-2">
                                                    <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: {{ min($report->completion_rate, 100) }}%"></div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Aksi Lihat Detail Agen Modal --}}
                                        <td class="py-4 px-6 whitespace-nowrap text-center print:hidden">
                                            <button type="button" 
                                                    @click="openAgentModal({{ $report->id }})" 
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 hover:bg-indigo-50 text-gray-700 hover:text-indigo-600 border border-gray-200 transition shadow-xs">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                <span>Detail</span>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="py-12 px-6 text-center text-gray-400 italic">
                                            Belum ada data agen terdaftar pada ruang lingkup Anda saat ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- ========================================================== --}}
            {{-- KONTEN TAB 2: LAPORAN RIWAYAT TIKET TERPERINCI             --}}
            {{-- ========================================================== --}}
            @if($tab === 'tickets')
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-base font-bold text-gray-800">Daftar Riwayat Tiket Pelayanan Live Chat</h3>
                            <p class="text-xs text-gray-400 mt-0.5">Daftar seluruh tiket pelanggan, status penanganan, agen petugas, dan tag kategori.</p>
                        </div>
                        <div class="text-xs text-gray-400">
                            Menampilkan <b>{{ $tickets->count() }}</b> dari total <b>{{ $summary['total_tickets'] }}</b> tiket
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/80 text-gray-600 uppercase text-xs font-semibold tracking-wider border-b border-gray-200">
                                    <th class="py-3.5 px-6">Nomor Tiket</th>
                                    <th class="py-3.5 px-6">Pelanggan WhatsApp</th>
                                    <th class="py-3.5 px-6">Device WhatsApp</th>
                                    <th class="py-3.5 px-6">Agen Penanggung Jawab</th>
                                    <th class="py-3.5 px-6 text-center">Tag Layanan</th>
                                    <th class="py-3.5 px-6 text-center">Status</th>
                                    <th class="py-3.5 px-6">Waktu Masuk</th>
                                    <th class="py-3.5 px-6 text-center print:hidden">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 text-sm divide-y divide-gray-100">
                                @forelse($tickets as $t)
                                    @php
                                        $currentTag = strtolower($t->tag ?? 'umum');
                                        $tagBadgeClass = $tagColors[$currentTag] ?? 'bg-gray-100 text-gray-700 border-gray-300';
                                    @endphp
                                    <tr class="hover:bg-gray-50/70 transition">
                                        {{-- Nomor Tiket --}}
                                        <td class="py-4 px-6 whitespace-nowrap font-mono font-bold text-indigo-700 text-xs">
                                            {{ $t->ticket_number }}
                                            @if($t->has_new_message)
                                                <span class="inline-block w-2 h-2 rounded-full bg-rose-500 ml-1 animate-pulse" title="Ada pesan baru"></span>
                                            @endif
                                        </td>

                                        {{-- Pelanggan WhatsApp --}}
                                        <td class="py-4 px-6 whitespace-nowrap">
                                            <div class="flex items-center gap-2.5">
                                                @if(!empty($t->avatar_url))
                                                    <img src="{{ $t->avatar_url }}" alt="{{ $t->customer_name }}" class="w-8 h-8 rounded-full object-cover border border-gray-200 shadow-xs" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs uppercase items-center justify-center border border-indigo-200 shadow-xs" style="display: none;">
                                                        {{ strtoupper(substr($t->customer_name ?: 'WA', 0, 2)) }}
                                                    </div>
                                                @else
                                                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs uppercase border border-indigo-200 shadow-xs">
                                                        {{ strtoupper(substr($t->customer_name ?: 'WA', 0, 2)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="font-bold text-gray-900 text-xs">{{ $t->customer_name ?: 'Pelanggan' }}</div>
                                                    <div class="text-[11px] font-mono text-gray-500">+{{ $t->number }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Device WhatsApp --}}
                                        <td class="py-4 px-6 whitespace-nowrap">
                                            @if($t->device)
                                                <div class="font-semibold text-gray-800 text-xs flex items-center gap-1.5">
                                                    <span class="w-2 h-2 rounded-full {{ $t->device->status === 'connected' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                                    <span>{{ $t->device->name }}</span>
                                                </div>
                                                <div class="text-[10px] text-gray-400 font-mono mt-0.5">
                                                    {{ $t->device->phone_number ? '+'.$t->device->phone_number : 'Slot: '.$t->device->gowa_session_id }}
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400 italic">Device #{{ $t->device_id }}</span>
                                            @endif
                                        </td>

                                        {{-- Agen Penanggung Jawab --}}
                                        <td class="py-4 px-6 whitespace-nowrap">
                                            @if($t->agent)
                                                <div class="flex items-center gap-2">
                                                    <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] uppercase flex items-center justify-center">
                                                        {{ substr($t->agent->name, 0, 2) }}
                                                    </div>
                                                    <span class="font-semibold text-gray-900 text-xs">{{ $t->agent->name }}</span>
                                                </div>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                    Belum Diambil
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Tag Layanan --}}
                                        <td class="py-4 px-6 whitespace-nowrap text-center">
                                            <span class="inline-block px-2.5 py-0.5 text-[11px] font-semibold rounded-full border {{ $tagBadgeClass }}">
                                                {{ ucfirst($t->tag ?? 'Umum') }}
                                            </span>
                                        </td>

                                        {{-- Status Tiket --}}
                                        <td class="py-4 px-6 whitespace-nowrap text-center">
                                            @if($t->status === 'open')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    Open
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    Closed
                                                </span>
                                            @endif
                                        </td>

                                        {{-- Waktu Masuk & Durasi --}}
                                        <td class="py-4 px-6 whitespace-nowrap text-xs">
                                            <div class="font-semibold text-gray-800">{{ $t->created_at->format('d M Y H:i') }}</div>
                                            <div class="text-[10px] text-gray-400 mt-0.5">{{ $t->created_at->diffForHumans() }}</div>
                                        </td>

                                        {{-- Link ke Live Chat --}}
                                        <td class="py-4 px-6 whitespace-nowrap text-center print:hidden">
                                            <a href="{{ route('messages.index', ['ticket_id' => $t->id]) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 transition" title="Buka Percakapan Live Chat">
                                                <span>Buka Chat</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-12 px-6 text-center text-gray-400 italic">
                                            Tidak ditemukan tiket yang sesuai dengan filter pencarian Anda.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginasi Tiket --}}
                    @if($tickets instanceof \Illuminate\Pagination\LengthAwarePaginator && $tickets->hasPages())
                        <div class="p-4 bg-gray-50 border-t border-gray-100">
                            {{ $tickets->links() }}
                        </div>
                    @endif
                </div>
            @endif

            {{-- ========================================================== --}}
            {{-- KONTEN TAB 3: DISTRIBUSI KATEGORI TAG & DEVICE             --}}
            {{-- ========================================================== --}}
            @if($tab === 'tags')
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Distribusi Tag Kategori Layanan --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base font-bold text-gray-800">Distribusi Tag Kategori Layanan</h3>
                                <p class="text-xs text-gray-400 mt-0.5">Proporsi topik obrolan live chat pelanggan.</p>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700">
                                Total: {{ $summary['total_tickets'] }} Tiket
                            </span>
                        </div>

                        <div class="space-y-4 mt-6">
                            @forelse($tagDistribution as $tagItem)
                                @php
                                    $tagNameLower = strtolower($tagItem->name);
                                    $badgeColor = $tagColors[$tagNameLower] ?? 'bg-slate-100 text-slate-700 border-slate-300';
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                                        <span class="inline-block px-2 py-0.5 rounded-full border {{ $badgeColor }}">
                                            {{ ucfirst($tagItem->name) }}
                                        </span>
                                        <div class="text-right">
                                            <span class="text-gray-900 font-bold">{{ $tagItem->count }} Tiket</span>
                                            <span class="text-gray-400 font-normal">({{ $tagItem->percentage }}%)</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: {{ $tagItem->percentage }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center text-xs text-gray-400 italic">
                                    Belum ada data tiket dengan tag yang tercatat.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Distribusi Beban Tiket Per Device WhatsApp --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base font-bold text-gray-800">Beban Layanan Per Device WhatsApp</h3>
                                <p class="text-xs text-gray-400 mt-0.5">Distribusi tiket yang masuk ke masing-masing device.</p>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700">
                                {{ $devices->count() }} Device Terdaftar
                            </span>
                        </div>

                        <div class="space-y-4 mt-6">
                            @forelse($deviceDistribution as $devItem)
                                <div>
                                    <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full {{ $devItem->status === 'connected' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                            <span class="text-gray-900 font-bold">{{ $devItem->device_name }}</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-gray-900 font-bold">{{ $devItem->count }} Tiket</span>
                                            <span class="text-gray-400 font-normal">({{ $devItem->percentage }}%)</span>
                                        </div>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2">
                                        <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $devItem->percentage }}%"></div>
                                    </div>
                                </div>
                            @empty
                                <div class="py-8 text-center text-xs text-gray-400 italic">
                                    Belum ada data device yang memiliki riwayat tiket.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- ============================================================== --}}
        {{-- MODAL INTERAKTIF DETAIL PERFORMA AGEN (ALPINE.JS)              --}}
        {{-- ============================================================== --}}
        <div x-show="showAgentModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             @keydown.escape.window="closeAgentModal()">
            {{-- Backdrop --}}
            <div x-show="showAgentModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" 
                 @click="closeAgentModal()"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                <div x-show="showAgentModal" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-3xl border border-gray-200">

                    {{-- Modal Loading State --}}
                    <div x-show="loading" class="p-12 text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-indigo-600 border-t-transparent mb-3"></div>
                        <p class="text-xs font-semibold text-gray-500">Memuat rincian data agen...</p>
                    </div>

                    {{-- Modal Content --}}
                    <div x-show="!loading && modalData">
                        {{-- Modal Header --}}
                        <div class="px-6 py-5 bg-gradient-to-r from-indigo-700 via-indigo-800 to-indigo-900 text-white flex items-center justify-between">
                            <div class="flex items-center gap-3.5">
                                <div class="w-12 h-12 rounded-xl bg-white/10 text-white font-extrabold text-lg flex items-center justify-center border border-white/20 shadow-inner" x-text="modalData?.agent?.initials"></div>
                                <div>
                                    <h3 class="font-bold text-lg text-white flex items-center gap-2">
                                        <span x-text="modalData?.agent?.name"></span>
                                        <template x-if="modalData?.agent?.is_active">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">Aktif</span>
                                        </template>
                                    </h3>
                                    <p class="text-xs text-indigo-200 font-mono mt-0.5" x-text="modalData?.agent?.email + ' • @' + modalData?.agent?.username"></p>
                                </div>
                            </div>
                            <button type="button" @click="closeAgentModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition">
                                &times;
                            </button>
                        </div>

                        {{-- 4 Kartu Metrik Agen --}}
                        <div class="p-6 bg-gray-50 border-b border-gray-200 grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                            <div class="bg-white p-3 rounded-xl border border-gray-200/80 shadow-xs">
                                <span class="text-[10px] text-gray-400 uppercase font-semibold block">Total Ditangani</span>
                                <span class="text-xl font-black text-gray-900 mt-0.5 block" x-text="modalData?.metrics?.total_handled"></span>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-gray-200/80 shadow-xs">
                                <span class="text-[10px] text-amber-600 uppercase font-semibold block">Tiket Open</span>
                                <span class="text-xl font-black text-amber-600 mt-0.5 block" x-text="modalData?.metrics?.open_count"></span>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-gray-200/80 shadow-xs">
                                <span class="text-[10px] text-emerald-600 uppercase font-semibold block">Tiket Closed</span>
                                <span class="text-xl font-black text-emerald-600 mt-0.5 block" x-text="modalData?.metrics?.closed_count"></span>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-gray-200/80 shadow-xs">
                                <span class="text-[10px] text-indigo-600 uppercase font-semibold block">Rasio CR</span>
                                <span class="text-xl font-black text-indigo-600 mt-0.5 block" x-text="modalData?.metrics?.completion_rate + '%'"></span>
                            </div>
                        </div>

                        {{-- Riwayat Tiket Terakhir Ditangani --}}
                        <div class="p-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="font-bold text-xs uppercase tracking-wider text-gray-700">10 Tiket Terakhir Ditangani</h4>
                                <span class="text-[11px] text-gray-400">Urut berdasarkan aktivitas terbaru</span>
                            </div>

                            <div class="overflow-x-auto max-h-64 overflow-y-auto rounded-xl border border-gray-200">
                                <table class="w-full text-left text-xs border-collapse">
                                    <thead class="bg-gray-100 text-gray-600 uppercase font-semibold tracking-wider sticky top-0">
                                        <tr>
                                            <th class="py-2.5 px-3">Nomor Tiket</th>
                                            <th class="py-2.5 px-3">Pelanggan</th>
                                            <th class="py-2.5 px-3">Device</th>
                                            <th class="py-2.5 px-3 text-center">Tag</th>
                                            <th class="py-2.5 px-3 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        <template x-for="t in modalData?.recent_tickets" :key="t.id">
                                            <tr class="hover:bg-gray-50 transition">
                                                <td class="py-2.5 px-3 font-mono font-bold text-indigo-600" x-text="t.ticket_number"></td>
                                                <td class="py-2.5 px-3">
                                                    <span class="font-bold text-gray-800 block" x-text="t.customer_name"></span>
                                                    <span class="text-[10px] text-gray-400 font-mono" x-text="'+' + t.number"></span>
                                                </td>
                                                <td class="py-2.5 px-3 text-gray-600" x-text="t.device_name"></td>
                                                <td class="py-2.5 px-3 text-center">
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-700" x-text="t.tag"></span>
                                                </td>
                                                <td class="py-2.5 px-3 text-center">
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase" 
                                                          :class="t.status === 'open' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'" 
                                                          x-text="t.status">
                                                    </span>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="!modalData?.recent_tickets || modalData?.recent_tickets.length === 0">
                                            <tr>
                                                <td colspan="5" class="py-6 text-center text-gray-400 italic">Belum ada riwayat tiket yang ditangani.</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-200 flex justify-end">
                            <button type="button" @click="closeAgentModal()" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-semibold transition">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Script Alpine.js Manager --}}
    <script>
        function reportManager() {
            return {
                showAgentModal: false,
                loading: false,
                modalData: null,
                openAgentModal(agentId) {
                    this.loading = true;
                    this.showAgentModal = true;
                    this.modalData = null;

                    fetch(`/reports/agent/${agentId}`)
                        .then(res => {
                            if (!res.ok) throw new Error('Gagal memuat detail agen');
                            return res.json();
                        })
                        .then(data => {
                            this.modalData = data;
                            this.loading = false;
                        })
                        .catch(err => {
                            alert('Terjadi kesalahan saat memuat data agen: ' + err.message);
                            this.loading = false;
                            this.showAgentModal = false;
                        });
                },
                closeAgentModal() {
                    this.showAgentModal = false;
                    this.modalData = null;
                }
            };
        }
    </script>
</x-app-layout>
