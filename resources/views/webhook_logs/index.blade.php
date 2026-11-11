<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                        {{ __('Webhook Log History') }}
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                        GOWA v9 Gateway
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    Pantau riwayat data payload webhook yang diterima secara realtime dari WhatsApp Gateway (GOWA v9).
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('webhook-logs.index') }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-gray-50 text-gray-700 font-semibold py-2 px-3.5 rounded-lg text-sm border border-gray-300 transition shadow-sm">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 12H16"></path></svg>
                    Refresh
                </a>
                <button type="button" @click="$dispatch('open-clear-modal')" class="inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    Bersihkan Log
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen" x-data="webhookLogManager()">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Message Success --}}
            @if (session('success'))
                <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3.5 rounded-xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
                </div>
            @endif

            {{-- Flash Message Error --}}
            @if (session('error'))
                <div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3.5 rounded-xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
                </div>
            @endif

            {{-- 1. KARTU STATISTIK RINGKASAN --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Total Log --}}
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Webhook</div>
                        <div class="text-2xl font-bold text-gray-900 mt-0.5">{{ number_format($stats['total'] ?? 0) }}</div>
                        <div class="text-[11px] text-gray-500">Seluruh data tercatat</div>
                    </div>
                </div>

                {{-- Pesan Masuk (Message) --}}
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Pesan Masuk</div>
                        <div class="text-2xl font-bold text-emerald-700 mt-0.5">{{ number_format($stats['messages'] ?? 0) }}</div>
                        <div class="text-[11px] text-emerald-600">Event message GOWA</div>
                    </div>
                </div>

                {{-- Event Sistem (Disconnect/Logout) --}}
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Event Sistem</div>
                        <div class="text-2xl font-bold text-amber-700 mt-0.5">{{ number_format($stats['events'] ?? 0) }}</div>
                        <div class="text-[11px] text-amber-600">Device logged out, dll</div>
                    </div>
                </div>

                {{-- Log Hari Ini --}}
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Log Hari Ini</div>
                        <div class="text-2xl font-bold text-blue-700 mt-0.5">{{ number_format($stats['today'] ?? 0) }}</div>
                        <div class="text-[11px] text-blue-600">Sejak 00:00 hari ini</div>
                    </div>
                </div>
            </div>

            {{-- 2. BAR PENCARIAN & FILTER MULTI-KRITERIA --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <form method="GET" action="{{ route('webhook-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5">
                    {{-- Pencarian Kata Kunci --}}
                    <div class="lg:col-span-4">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Cari Nomor / Pesan</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor, pengirim, kata..." class="w-full pl-9 pr-3 py-2 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                        </div>
                    </div>

                    {{-- Filter Device --}}
                    <div class="lg:col-span-3">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Pilih Device</label>
                        <select name="device_id" class="w-full py-2 px-3 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                            <option value="">Semua Device ({{ $devices->count() }})</option>
                            @foreach($devices as $dev)
                                <option value="{{ $dev->id }}" {{ request('device_id') == $dev->id ? 'selected' : '' }}>
                                    {{ $dev->name }} ({{ $dev->phone_number ? '+'.$dev->phone_number : 'Slot: '.$dev->gowa_session_id }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Event --}}
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Tipe Event</label>
                        <select name="event" class="w-full py-2 px-3 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                            <option value="">Semua Event</option>
                            <option value="message" {{ request('event') === 'message' ? 'selected' : '' }}>message (Pesan)</option>
                            <option value="device.logged_out" {{ request('event') === 'device.logged_out' ? 'selected' : '' }}>device.logged_out (Logout)</option>
                        </select>
                    </div>

                    {{-- Filter Rentang Waktu --}}
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">Rentang Waktu</label>
                        <select name="date_range" class="w-full py-2 px-3 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                            <option value="">Semua Waktu</option>
                            <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Hari Ini</option>
                            <option value="7days" {{ request('date_range') === '7days' ? 'selected' : '' }}>7 Hari Terakhir</option>
                            <option value="30days" {{ request('date_range') === '30days' ? 'selected' : '' }}>30 Hari Terakhir</option>
                        </select>
                    </div>

                    {{-- Tombol Aksi Filter --}}
                    <div class="lg:col-span-1 flex items-end gap-2">
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-3 rounded-lg text-sm transition shadow-sm flex items-center justify-center gap-1" title="Terapkan Filter">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                            <span class="sm:hidden lg:inline">Filter</span>
                        </button>
                        @if(request()->anyFilled(['search', 'device_id', 'event', 'date_range']))
                            <a href="{{ route('webhook-logs.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-2.5 rounded-lg text-sm transition border border-gray-300" title="Reset Filter">
                                &times;
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- 3. TABEL UTAMA RIWAYAT WEBHOOK --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 text-gray-600 uppercase text-xs font-semibold tracking-wider border-b border-gray-200">
                                <th class="py-3.5 px-5">Waktu &amp; ID</th>
                                <th class="py-3.5 px-5">Event</th>
                                <th class="py-3.5 px-5">Device SIMAWA</th>
                                <th class="py-3.5 px-5">Pengirim</th>
                                <th class="py-3.5 px-5">Konten Pesan / Media</th>
                                <th class="py-3.5 px-5 text-center">Payload JSON</th>
                                <th class="py-3.5 px-5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm divide-y divide-gray-100">
                            @forelse ($logs as $log)
                                @php
                                    $logData = [
                                        'id' => $log->id,
                                        'created_at' => $log->created_at->format('d M Y H:i:s'),
                                        'created_at_relative' => $log->created_at->diffForHumans(),
                                        'event' => $log->event,
                                        'device_name' => $log->device?->name ?? 'Device #'.$log->device_id,
                                        'device_phone' => $log->device?->phone_number,
                                        'session_id' => $log->session_id,
                                        'sender_number' => $log->sender_number,
                                        'sender_name' => $log->sender_display_name,
                                        'message' => $log->message,
                                        'media_type' => $log->media_type,
                                        'media_info' => $log->media_info,
                                        'message_id' => $log->message_id,
                                        'chat_id' => $log->chat_id,
                                        'quoted_body' => $log->quoted_body,
                                        'is_group' => $log->is_group,
                                        'is_from_me' => $log->is_from_me,
                                        'payload' => $log->payload_data,
                                    ];
                                @endphp
                                <tr class="hover:bg-gray-50/80 transition">
                                    {{-- Kolom 1: Waktu & ID Pesan --}}
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        <div class="font-bold text-gray-900 text-xs">
                                            {{ $log->created_at->format('d M Y') }}
                                        </div>
                                        <div class="text-xs text-gray-600 font-mono">
                                            {{ $log->created_at->format('H:i:s') }}
                                        </div>
                                        <div class="text-[11px] text-gray-400 mt-0.5">
                                            {{ $log->created_at->diffForHumans() }}
                                        </div>
                                        @if($log->message_id)
                                            <div class="mt-1">
                                                <span class="inline-block px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 text-[10px] font-mono" title="Message ID: {{ $log->message_id }}">
                                                    #{{ Str::limit($log->message_id, 12, '..') }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Kolom 2: Event GOWA --}}
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        @if($log->event === 'message')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                message
                                            </span>
                                        @elseif($log->event === 'device.logged_out')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                device.logged_out
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                                {{ $log->event }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Kolom 3: Device SIMAWA --}}
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        @if($log->device)
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full {{ $log->device->status === 'connected' ? 'bg-emerald-500' : 'bg-rose-500' }}" title="{{ $log->device->status }}"></span>
                                                <span class="font-bold text-gray-900 text-sm">{{ $log->device->name }}</span>
                                            </div>
                                            <div class="text-[11px] font-mono text-gray-500 mt-0.5">
                                                {{ $log->device->phone_number ? '+'.$log->device->phone_number : 'Slot: '.$log->session_id }}
                                            </div>
                                        @else
                                            <div class="text-xs font-semibold text-gray-400">Device ID: {{ $log->device_id }}</div>
                                            <div class="text-[10px] text-gray-400 italic">(Telah dihapus)</div>
                                        @endif
                                    </td>

                                    {{-- Kolom 4: Pengirim (Sender Display Name & Nomor) --}}
                                    <td class="py-4 px-5 whitespace-nowrap">
                                        @if($log->sender_display_name)
                                            <div class="font-bold text-gray-900 text-sm flex items-center gap-1.5">
                                                <span>{{ $log->sender_display_name }}</span>
                                            </div>
                                        @endif
                                        <div class="text-xs font-mono {{ $log->sender_display_name ? 'text-gray-500' : 'font-bold text-gray-900' }}">
                                            +{{ $log->sender_number }}
                                        </div>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            @if($log->is_group)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                                    Grup WA
                                                </span>
                                            @endif
                                            @if($log->is_from_me)
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                    Pesan Keluar
                                                </span>
                                            @endif
                                        </div>
                                        @if($log->is_from_me && $log->target_number)
                                            <div class="text-[11px] text-gray-500 font-mono mt-0.5" title="Nomor Tujuan">
                                                Tujuan: <span class="text-blue-600 font-semibold">+{{ $log->target_number }}</span>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Kolom 5: Konten Pesan / Media / Kutipan --}}
                                    <td class="py-4 px-5 max-w-sm">
                                        {{-- Indikator Media --}}
                                        @if($log->media_type !== 'text')
                                            <div class="mb-1">
                                                @switch($log->media_type)
                                                    @case('image')
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                            📷 Gambar
                                                        </span>
                                                        @break
                                                    @case('document')
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                                                            📄 Dokumen
                                                        </span>
                                                        @break
                                                    @case('video')
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-purple-100 text-purple-800">
                                                            🎥 Video
                                                        </span>
                                                        @break
                                                    @case('audio')
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800">
                                                            🎵 Audio
                                                        </span>
                                                        @break
                                                    @case('sticker')
                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-semibold bg-pink-100 text-pink-800">
                                                            ✨ Stiker
                                                        </span>
                                                        @break
                                                @endswitch
                                            </div>
                                        @endif

                                        {{-- Kutipan Pesan (Quote Reply) --}}
                                        @if($log->quoted_body)
                                            <div class="mb-1 text-[11px] text-gray-500 bg-gray-100 border-l-2 border-indigo-400 pl-2 py-0.5 rounded-r truncate" title="{{ $log->quoted_body }}">
                                                ↳ {{ Str::limit($log->quoted_body, 35) }}
                                            </div>
                                        @endif

                                        {{-- Teks Pesan --}}
                                        @if($log->message)
                                            <div class="text-sm text-gray-800 break-words line-clamp-2" title="{{ $log->message }}">
                                                {{ $log->message }}
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic text-xs">(Tanpa teks pesan)</span>
                                        @endif
                                    </td>

                                    {{-- Kolom 6: Payload JSON Detail Modal --}}
                                    <td class="py-4 px-5 whitespace-nowrap text-center">
                                        <button type="button" 
                                                @click="openModal(@js($logData))"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 hover:bg-indigo-50 text-gray-700 hover:text-indigo-600 border border-gray-200 hover:border-indigo-200 transition shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                                            Lihat JSON
                                        </button>
                                    </td>

                                    {{-- Kolom 7: Aksi Hapus Baris --}}
                                    <td class="py-4 px-5 whitespace-nowrap text-center">
                                        <form action="{{ route('webhook-logs.destroy', $log) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan log webhook ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus Catatan Ini">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 px-6 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-14 h-14 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mb-3">
                                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </div>
                                            <h4 class="font-bold text-gray-800 text-base">Belum Ada Riwayat Webhook</h4>
                                            <p class="text-xs text-gray-500 mt-1 max-w-sm">
                                                Setiap data pesan masuk atau event dari WhatsApp Gateway (GOWA v9) akan otomatis dicatat dan ditampilkan di halaman ini.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Navigasi --}}
                @if ($logs->hasPages())
                    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>

        </div>

        {{-- ============================================================== --}}
        {{-- MODAL INTERAKTIF DETAIL JSON PAYLOAD (ALPINE.JS)               --}}
        {{-- ============================================================== --}}
        <div x-show="showModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             aria-labelledby="modal-title" 
             role="dialog" 
             aria-modal="true"
             @keydown.escape.window="closeModal()">
            {{-- Backdrop --}}
            <div x-show="showModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" 
                 @click="closeModal()"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-4xl border border-gray-200">

                    {{-- Modal Header --}}
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center font-mono font-bold text-xs">
                                {}
                            </span>
                            <div>
                                <h3 class="font-bold text-gray-900 text-base flex items-center gap-2">
                                    <span>Detail Webhook Payload GOWA v9</span>
                                    <template x-if="activeLog">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-mono font-semibold"
                                              :class="activeLog.event === 'message' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                                              x-text="activeLog.event">
                                        </span>
                                    </template>
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5" x-text="activeLog ? 'Diterima: ' + activeLog.created_at + ' (' + activeLog.created_at_relative + ')' : ''"></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="copyJson()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white hover:bg-gray-100 text-gray-700 border border-gray-300 transition shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                <span x-text="copied ? 'Tersalin!' : 'Salin JSON'"></span>
                            </button>
                            <button type="button" @click="closeModal()" class="w-8 h-8 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-200/60 flex items-center justify-center transition">
                                &times;
                            </button>
                        </div>
                    </div>

                    {{-- Info Bar Ekstraksi --}}
                    <div class="px-6 py-3 bg-gray-100/75 border-b border-gray-200 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                        <div>
                            <span class="text-gray-400 uppercase tracking-wider text-[10px] font-bold block">Device Target:</span>
                            <span class="font-bold text-gray-800 truncate block" x-text="activeLog?.device_name"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 uppercase tracking-wider text-[10px] font-bold block">Pengirim:</span>
                            <span class="font-mono font-semibold text-gray-800 truncate block" x-text="activeLog ? (activeLog.sender_name ? activeLog.sender_name + ' (+' + activeLog.sender_number + ')' : '+' + activeLog.sender_number) : '-'"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 uppercase tracking-wider text-[10px] font-bold block">Session ID:</span>
                            <span class="font-mono text-gray-700 truncate block" x-text="activeLog?.session_id || '-'"></span>
                        </div>
                        <div>
                            <span class="text-gray-400 uppercase tracking-wider text-[10px] font-bold block">Tipe Pesan:</span>
                            <span class="font-semibold text-indigo-700 uppercase block" x-text="activeLog?.media_type || 'text'"></span>
                        </div>
                    </div>

                    {{-- Body JSON Code Box --}}
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-gray-700 uppercase tracking-wide">Raw JSON Payload (Pretty Print):</span>
                            <span class="text-[11px] text-gray-400 font-mono">Format: application/json</span>
                        </div>
                        <div class="bg-gray-900 rounded-xl p-4 border border-gray-800 overflow-x-auto max-h-[60vh]">
                            <pre class="text-[11px] text-emerald-400 font-mono leading-relaxed select-all" x-text="activeLog ? JSON.stringify(activeLog.payload, null, 2) : ''"></pre>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                        <span class="text-xs text-gray-500">
                            Payload disimpan otomatis pada tabel <code class="font-mono text-gray-700 bg-gray-200 px-1 py-0.5 rounded">webhook_logs</code>.
                        </span>
                        <button type="button" @click="closeModal()" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-xs font-semibold transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL KONFIRMASI BERSIHKAN LOG                                  --}}
        {{-- ============================================================== --}}
        <div x-show="showClearModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto" 
             @open-clear-modal.window="showClearModal = true"
             @keydown.escape.window="showClearModal = false">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="showClearModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4 text-center">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 w-full sm:max-w-lg border border-gray-200 p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-lg">Bersihkan Riwayat Webhook?</h3>
                            <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                                Tindakan ini akan menghapus data catatan webhook log dari database. Data yang sudah dihapus tidak dapat dipulihkan kembali.
                            </p>
                        </div>
                    </div>

                    <form action="{{ route('webhook-logs.clear') }}" method="POST" class="mt-6 space-y-4">
                        @csrf
                        @if(request('device_id'))
                            <input type="hidden" name="device_id" value="{{ request('device_id') }}">
                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                                ℹ️ Menghapus log hanya untuk device yang sedang difilter.
                            </div>
                        @else
                            <div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-800">
                                ⚠️ Anda akan membersihkan <b>seluruh</b> riwayat log webhook di database.
                            </div>
                        @endif

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="button" @click="showClearModal = false" class="px-4 py-2.5 rounded-lg text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2.5 rounded-lg text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 transition shadow">
                                Ya, Bersihkan Log
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    {{-- Script Alpine.js Manager --}}
    <script>
        function webhookLogManager() {
            return {
                showModal: false,
                showClearModal: false,
                copied: false,
                activeLog: null,
                openModal(log) {
                    this.activeLog = log;
                    this.copied = false;
                    this.showModal = true;
                },
                closeModal() {
                    this.showModal = false;
                    this.activeLog = null;
                },
                copyJson() {
                    if (!this.activeLog) return;
                    const jsonString = JSON.stringify(this.activeLog.payload, null, 2);
                    navigator.clipboard.writeText(jsonString).then(() => {
                        this.copied = true;
                        setTimeout(() => {
                            this.copied = false;
                        }, 2500);
                    });
                }
            };
        }
    </script>
</x-app-layout>
