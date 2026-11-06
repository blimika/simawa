<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    {{ __('Dashboard Super Administrator') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Kontrol dan pengawasan global seluruh sistem SIMAWA (Admin, Agen, Device WhatsApp, dan Antrean Layanan).
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('messages.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <span>Live Chat</span>
                </a>
                <a href="{{ route('devices.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Kelola Device</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- 6 KARTU STATISTIK GLOBAL -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                
                <!-- 1. Total Device Global -->
                <div class="bg-white rounded-2xl p-4 border border-gray-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                            {{ $connectedDevices }} Online
                        </span>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ $totalDevice }}</div>
                        <div class="text-xs font-semibold text-gray-700 mt-0.5">Total Device</div>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $disconnectedDevices }} offline/terputus</p>
                    </div>
                </div>

                <!-- 2. Belum Terlayani Global -->
                <div class="bg-white rounded-2xl p-4 border border-amber-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                            Antrean
                        </span>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-extrabold text-amber-600 tracking-tight">{{ $tiketBelumDiambil }}</div>
                        <div class="text-xs font-semibold text-gray-700 mt-0.5">Belum Terlayani</div>
                        <p class="text-[10px] text-gray-400 mt-0.5">Total {{ $totalTiketOpen }} tiket open</p>
                    </div>
                </div>

                <!-- 3. Sedang Dilayani Global -->
                <div class="bg-white rounded-2xl p-4 border border-blue-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-800">
                            Aktif
                        </span>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-extrabold text-blue-600 tracking-tight">{{ $tiketSedangDilayani }}</div>
                        <div class="text-xs font-semibold text-gray-700 mt-0.5">Sedang Dilayani</div>
                        <p class="text-[10px] text-gray-400 mt-0.5">Ditangani agen</p>
                    </div>
                </div>

                <!-- 4. Sudah Terlayani Hari Ini -->
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
                        <p class="text-[10px] text-gray-400 mt-0.5">Total {{ $tiketSelesaiTotal }} tuntas</p>
                    </div>
                </div>

                <!-- 5. Pesan Hari Ini Global -->
                <div class="bg-white rounded-2xl p-4 border border-indigo-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-100 text-indigo-800">
                            Trafik
                        </span>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-extrabold text-indigo-600 tracking-tight">{{ $pesanHariIni }}</div>
                        <div class="text-xs font-semibold text-gray-700 mt-0.5">Pesan Hari Ini</div>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $pesanMasukHariIni }} in &bull; {{ $pesanKeluarHariIni }} out</p>
                    </div>
                </div>

                <!-- 6. Total User (Admin & Agen) -->
                <div class="bg-white rounded-2xl p-4 border border-rose-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800">
                            Pengguna
                        </span>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-extrabold text-rose-600 tracking-tight">{{ $totalAdmin + $totalAgent }}</div>
                        <div class="text-xs font-semibold text-gray-700 mt-0.5">Admin & Agen</div>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $totalAdmin }} Admin &bull; {{ $totalAgent }} Agen</p>
                    </div>
                </div>

            </div>

            <!-- DUA KOLOM: ANTREAN TIKET & DAFTAR DEVICE GLOBAL -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- ANTREAN TIKET TERKINI (2 COLS) -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Antrean Tiket Terkini (Seluruh Sistem)</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Tiket terbuka yang sedang berjalan di semua instansi</p>
                            </div>
                            <a href="{{ route('messages.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                                Buka Live Chat &rarr;
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
                                                Belum Terlayani
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                                Agen: {{ $ticket->agent->name ?? 'Operator' }}
                                            </span>
                                        @endif
                                        <a href="{{ route('messages.index', ['ticket_id' => $ticket->id]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                                            <span>Lihat Chat</span>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-xs text-gray-400">
                                    Tidak ada antrean tiket saat ini.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- DAFTAR DEVICE WHATSAPP (1 COL) -->
                <div class="space-y-6">
                    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-gray-900">Perangkat WhatsApp Terdaftar</h3>
                            <a href="{{ route('devices.index') }}" class="text-xs text-purple-600 hover:underline font-semibold">
                                Kelola
                            </a>
                        </div>

                        <div class="space-y-3">
                            @forelse($devices as $dev)
                                @php
                                    $isConnected = ($dev->status === 'connected');
                                @endphp
                                <div class="p-3 rounded-xl border {{ $isConnected ? 'border-emerald-100 bg-emerald-50/40' : 'border-rose-100 bg-rose-50/40' }} flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <h4 class="text-xs font-bold text-gray-900 truncate">{{ $dev->name }}</h4>
                                        <p class="text-[11px] text-gray-500 font-mono truncate">{{ $dev->phone_number ?: 'Nomor belum sinkron' }}</p>
                                        <p class="text-[10px] text-gray-400 mt-0.5">Pemilik: {{ $dev->owner->name ?? 'Admin' }}</p>
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
                                    Belum ada device.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
