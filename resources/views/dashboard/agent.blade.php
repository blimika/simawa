<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    {{ __('Workspace Layanan Agen') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Selamat bertugas, <span class="font-semibold text-gray-700">{{ auth()->user()->name }}</span>! Pantau antrean pesan pelanggan dan respon tiket secara real-time.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('messages.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    <span>Buka Live Chat</span>
                    @if($tiketBelumDiambil > 0)
                        <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-rose-500 rounded-full animate-pulse">
                            {{ $tiketBelumDiambil }}
                        </span>
                    @endif
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Banner Notifikasi Antrean Tiket -->
            @if($tiketBelumDiambil > 0)
                <div class="p-4 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-l-4 border-amber-500 rounded-r-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm bg-white">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">
                                Ada {{ $tiketBelumDiambil }} Pesan/Tiket Belum Terlayani!
                            </h4>
                            <p class="text-xs text-gray-600 mt-0.5">
                                Pelanggan sedang menunggu respon. Silakan klaim dan balas pesan sekarang di ruang Live Chat.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('messages.index') }}" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg shadow-sm transition whitespace-nowrap">
                        <span>Layani Antrean Sekarang</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            @endif

            <!-- 4 KARTU STATISTIK UTAMA PELAYANAN AGEN -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- 1. Tiket Belum Terlayani / Antrean Menunggu -->
                <div class="bg-white rounded-2xl p-5 border border-amber-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        @if($tiketBelumDiambil > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                Butuh Respon
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                Bersih
                            </span>
                        @endif
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ $tiketBelumDiambil }}</div>
                        <div class="text-sm font-semibold text-gray-800 mt-1">Belum Terlayani</div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Menunggu diambil oleh agen (total {{ $totalBelumTerlayani }} open)
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs font-medium">
                        <span class="text-gray-500">Antrean masuk</span>
                        <a href="{{ route('messages.index') }}" class="text-amber-600 hover:text-amber-700 font-semibold inline-flex items-center gap-1">
                            Buka Antrean &rarr;
                        </a>
                    </div>
                </div>

                <!-- 2. Sedang Anda Layani (Aktif) -->
                <div class="bg-white rounded-2xl p-5 border border-blue-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                            Sedang Berjalan
                        </span>
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl font-extrabold text-blue-600 tracking-tight">{{ $tiketSayaAktif }}</div>
                        <div class="text-sm font-semibold text-gray-800 mt-1">Sedang Anda Layani</div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Tiket aktif dalam penanganan Anda
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs font-medium">
                        <span class="text-gray-500">Tiket aktif Anda</span>
                        <a href="{{ route('messages.index') }}" class="text-blue-600 hover:text-blue-700 font-semibold inline-flex items-center gap-1">
                            Lanjutkan Chat &rarr;
                        </a>
                    </div>
                </div>

                <!-- 3. Sudah Terlayani & Selesai Hari Ini -->
                <div class="bg-white rounded-2xl p-5 border border-emerald-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            Hari Ini
                        </span>
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl font-extrabold text-emerald-600 tracking-tight">{{ $tiketSayaSelesaiHariIni }}</div>
                        <div class="text-sm font-semibold text-gray-800 mt-1">Sudah Terlayani (Selesai)</div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Total {{ $tiketSayaSelesaiTotal }} tiket diselesaikan oleh Anda
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs font-medium">
                        <span class="text-gray-500">Pencapaian layanan</span>
                        <span class="text-emerald-700 font-semibold">Terselesaikan</span>
                    </div>
                </div>

                <!-- 4. Respon Pesan Terkirim Hari Ini -->
                <div class="bg-white rounded-2xl p-5 border border-purple-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                            Aktivitas
                        </span>
                    </div>
                    <div class="mt-4">
                        <div class="text-3xl font-extrabold text-purple-600 tracking-tight">{{ $pesanSayaHariIni }}</div>
                        <div class="text-sm font-semibold text-gray-800 mt-1">Pesan Terkirim Hari Ini</div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $pesanMasukHariIni }} pesan pelanggan masuk ke device
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs font-medium">
                        <span class="text-gray-500">Aktivitas chat hari ini</span>
                        <span class="text-purple-700 font-semibold">Aktif</span>
                    </div>
                </div>

            </div>

            <!-- KONTEN UTAMA: ANTREAN TIKET & STATUS DEVICE -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- KOLOM KIRI (2 COLS): ANTREAN TIKET BUTUH RESPON -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm overflow-hidden">
                        <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <span>Antrean Tiket & Percakapan Butuh Respon</span>
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Tiket yang belum diambil agen atau sedang aktif dalam penanganan Anda
                                </p>
                            </div>
                            <a href="{{ route('messages.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1 self-start sm:self-auto">
                                <span>Buka Semua di Live Chat</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>

                        <!-- LIST ANTREAN TIKET -->
                        <div class="divide-y divide-gray-100">
                            @forelse($antreanTiket as $ticket)
                                @php
                                    $initials = strtoupper(substr(trim($ticket->customer_name ?: ($ticket->number ?: 'P')), 0, 2));
                                    $isUnassigned = is_null($ticket->agent_id);
                                    $isHandledByMe = $ticket->agent_id == auth()->id();
                                @endphp
                                <div class="p-4 hover:bg-gray-50/80 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <!-- Avatar Kontak -->
                                        <div class="relative shrink-0">
                                            @if(!empty($ticket->avatar_url))
                                                <img src="{{ $ticket->avatar_url }}" alt="{{ $ticket->customer_name }}" class="w-11 h-11 rounded-full object-cover ring-2 ring-gray-100 shadow-sm" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                                                <div class="hidden w-11 h-11 rounded-full bg-gradient-to-br from-emerald-600 to-teal-700 text-white font-bold text-sm flex items-center justify-center ring-2 ring-gray-100 shadow-sm">
                                                    {{ $initials }}
                                                </div>
                                            @else
                                                <div class="w-11 h-11 rounded-full bg-gradient-to-br from-emerald-600 to-teal-700 text-white font-bold text-sm flex items-center justify-center ring-2 ring-gray-100 shadow-sm">
                                                    {{ $initials }}
                                                </div>
                                            @endif
                                            @if($ticket->has_new_message)
                                                <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-rose-500 border-2 border-white rounded-full animate-ping"></span>
                                                <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-rose-500 border-2 border-white rounded-full"></span>
                                            @endif
                                        </div>

                                        <!-- Informasi Tiket -->
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h4 class="text-sm font-bold text-gray-900 truncate">
                                                    {{ $ticket->customer_name ?: 'Pelanggan WhatsApp' }}
                                                </h4>
                                                <span class="text-xs text-gray-400 font-mono">
                                                    ({{ $ticket->number }})
                                                </span>
                                                @if($ticket->has_new_message)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700">
                                                        Pesan Baru
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2 text-xs text-gray-500 mt-1 flex-wrap">
                                                <span class="inline-flex items-center px-1.5 py-0.5 bg-gray-100 text-gray-700 rounded font-mono font-medium">
                                                    {{ $ticket->ticket_number }}
                                                </span>
                                                <span>&bull;</span>
                                                <span class="text-gray-500">
                                                    Device: <strong class="text-gray-700 font-medium">{{ $ticket->device->name ?? 'WhatsApp' }}</strong>
                                                </span>
                                                <span>&bull;</span>
                                                <span class="text-gray-400">
                                                    {{ $ticket->updated_at ? $ticket->updated_at->diffForHumans() : '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Status & Tombol Aksi -->
                                    <div class="flex items-center gap-3 shrink-0 self-end sm:self-auto">
                                        @if($isUnassigned)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>
                                                Belum Terlayani
                                            </span>
                                            <a href="{{ route('messages.index', ['ticket_id' => $ticket->id]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-600 hover:bg-amber-700 active:bg-amber-800 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                                                <span>Ambil & Balas</span>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                </svg>
                                            </a>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span>
                                                Sedang Anda Layani
                                            </span>
                                            <a href="{{ route('messages.index', ['ticket_id' => $ticket->id]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                                                <span>Buka Chat</span>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center">
                                    <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <h4 class="text-base font-bold text-gray-900">Semua Tiket Terlayani!</h4>
                                    <p class="text-xs text-gray-500 max-w-sm mx-auto mt-1">
                                        Tidak ada tiket antrean yang menunggu respon saat ini. Pesan baru yang masuk dari pelanggan akan otomatis muncul di sini.
                                    </p>
                                    <a href="{{ route('messages.index') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition">
                                        <span>Buka Ruang Live Chat</span>
                                    </a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN (1 COL): STATUS DEVICE & RIWAYAT SELESAI -->
                <div class="space-y-6">

                    <!-- STATUS PERANGKAT WHATSAPP YANG DITANGANI -->
                    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span>Device WhatsApp Ditugaskan</span>
                            </h3>
                            <span class="text-xs font-semibold px-2 py-0.5 bg-gray-100 text-gray-700 rounded-md">
                                {{ $devices->count() }} Device
                            </span>
                        </div>

                        <div class="space-y-3">
                            @forelse($devices as $dev)
                                @php
                                    $isConnected = ($dev->status === 'connected');
                                @endphp
                                <div class="p-3 rounded-xl border {{ $isConnected ? 'border-emerald-100 bg-emerald-50/40' : 'border-rose-100 bg-rose-50/40' }} flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-lg {{ $isConnected ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }} flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                                Online
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800">
                                                Offline
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-xs text-gray-500">
                                    Belum ada device WhatsApp yang ditugaskan kepada Anda.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- RIWAYAT TIKET SELESAI OLEH ANDA -->
                    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-sm p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Terakhir Diselesaikan oleh Anda</span>
                            </h3>
                            <span class="text-xs text-emerald-600 font-semibold">
                                Total: {{ $tiketSayaSelesaiTotal }}
                            </span>
                        </div>

                        <div class="space-y-2.5">
                            @forelse($riwayatTiketSelesai as $item)
                                <div class="p-3 rounded-xl bg-gray-50/80 border border-gray-100 flex items-center justify-between gap-3 text-xs">
                                    <div class="min-w-0">
                                        <div class="font-bold text-gray-900 truncate">
                                            {{ $item->customer_name ?: $item->number }}
                                        </div>
                                        <div class="text-[11px] text-gray-400 font-mono mt-0.5">
                                            {{ $item->ticket_number }} &bull; {{ $item->updated_at ? $item->updated_at->diffForHumans() : '-' }}
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 shrink-0">
                                        Selesai
                                    </span>
                                </div>
                            @empty
                                <div class="text-center py-4 text-xs text-gray-400">
                                    Belum ada tiket yang Anda selesaikan hari ini.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- PANDUAN CEPAT AGEN -->
                    <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-2xl border border-indigo-100 p-5 text-xs text-gray-700 space-y-2">
                        <h4 class="font-bold text-indigo-900 flex items-center gap-1.5 text-sm">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Tips Pelayanan Cepat</span>
                        </h4>
                        <ul class="list-disc list-inside space-y-1 text-gray-600 leading-relaxed">
                            <li>Klik tombol <strong>"Ambil & Balas"</strong> pada tiket antrean untuk mulai melayani pelanggan.</li>
                            <li>Tanda titik merah menandakan pelanggan baru saja mengirim pesan baru.</li>
                            <li>Jika percakapan tuntas, klik tombol <strong>"Selesaikan Tiket"</strong> agar status tiket berubah menjadi Terlayani.</li>
                        </ul>
                    </div>

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
