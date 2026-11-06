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
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Live Chat') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Layanan ruang obrolan langsung WhatsApp dengan antrean tiket dan manajemen agen.
                </p>
            </div>
            <button onclick="document.getElementById('newMessageModal').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-xl text-sm transition duration-150 shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                + Pesan Baru
            </button>
        </div>
    </x-slot>

    <div class="py-6 sm:py-8 bg-gray-50 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-200">

                <div class="h-[calc(100vh-210px)] min-h-[580px] flex overflow-hidden bg-white">

                    {{-- ======================================================== --}}
                    {{-- SIDEBAR KIRI: ANTREAN PERCAKAPAN & TIKET                 --}}
                    {{-- ======================================================== --}}
                    <div class="w-full sm:w-80 md:w-96 lg:w-[380px] xl:w-[420px] shrink-0 bg-gray-50 border-r border-gray-200 flex flex-col">

                        {{-- Header Sidebar & Pencarian --}}
                        <div class="p-4 border-b border-gray-200 bg-white shadow-xs z-10 flex flex-col gap-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                        <span>Antrean Pesan</span>
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-bold border border-indigo-200">
                                            {{ $tickets->count() }}
                                        </span>
                                    </h2>
                                    <p class="text-[11px] text-gray-400">Pilih pesan untuk melayani pelanggan</p>
                                </div>
                            </div>

                            <form action="{{ route('messages.index') }}" method="GET" class="relative">
                                @if(request('ticket_id'))
                                    <input type="hidden" name="ticket_id" value="{{ request('ticket_id') }}">
                                @endif

                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, nomor, atau tiket..." class="w-full text-xs border-gray-300 rounded-xl shadow-xs focus:ring-indigo-500 focus:border-indigo-500 pl-9 py-2 transition-all">
                                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>

                                @if(request('search'))
                                    <a href="{{ route('messages.index', request('ticket_id') ? ['ticket_id' => request('ticket_id')] : []) }}" class="absolute right-3 top-2.5 text-gray-400 hover:text-red-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </a>
                                @endif
                            </form>
                        </div>

                        @if(session('error'))
                            <div class="bg-red-100 text-red-700 text-xs px-4 py-2 border-b border-red-200">{{ session('error') }}</div>
                        @endif
                        @if(session('success'))
                            <div class="bg-green-100 text-green-700 text-xs px-4 py-2 border-b border-green-200">{{ session('success') }}</div>
                        @endif

                        {{-- Daftar Tiket di Sidebar --}}
                        <div class="flex-1 overflow-y-auto divide-y divide-gray-100" id="sidebar-list">
                            @forelse($tickets as $t)
                                @php
                                    $contact = $contacts->firstWhere('number', $t->number);
                                    $displayName = $contact ? $contact->name : ($t->customer_name ?: 'Pelanggan Baru');
                                    $avatarUrl = $t->avatar_url ?: ($contact->avatar_url ?? null);

                                    $currentTag = strtolower($t->tag ?? 'umum');
                                    $tagClass = $tagColors[$currentTag] ?? $tagColors['umum'];
                                @endphp

                                <a href="{{ route('messages.index', ['ticket_id' => $t->id, 'search' => request('search')]) }}" class="block p-3.5 hover:bg-gray-100/80 transition {{ request('ticket_id') == $t->id ? 'bg-indigo-50/70 border-l-4 border-indigo-500 shadow-xs' : '' }} {{ $t->status === 'closed' ? 'opacity-70 bg-gray-50/60' : '' }}">
                                    <div class="flex items-start gap-3">
                                        {{-- Avatar Kontak di Sidebar --}}
                                        <div class="relative shrink-0 mt-0.5">
                                            @if(!empty($avatarUrl))
                                                <img src="{{ $avatarUrl }}" 
                                                     alt="{{ $displayName }}" 
                                                     class="w-11 h-11 rounded-full object-cover border border-gray-200 shadow-xs" 
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="w-11 h-11 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs uppercase items-center justify-center border border-emerald-200 shadow-xs" style="display: none;">
                                                    {{ strtoupper(substr($displayName, 0, 2)) }}
                                                </div>
                                            @else
                                                <div class="w-11 h-11 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs uppercase border border-emerald-200 shadow-xs">
                                                    {{ strtoupper(substr($displayName, 0, 2)) }}
                                                </div>
                                            @endif

                                            {{-- Indikator Pesan Baru --}}
                                            @if($t->has_new_message)
                                                <span class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 bg-blue-600 border-2 border-white rounded-full animate-ping"></span>
                                                <span class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 bg-blue-600 border-2 border-white rounded-full"></span>
                                            @endif
                                        </div>

                                        {{-- Detail Info Tiket --}}
                                        <div class="flex-1 min-w-0">
                                            <div class="flex justify-between items-start mb-0.5">
                                                <span class="font-bold text-sm truncate {{ $t->has_new_message ? 'text-indigo-950 font-black' : 'text-gray-900' }} flex items-center gap-1">
                                                    <span class="truncate">{{ $displayName }}</span>
                                                    @if($contact)
                                                        <span class="bg-emerald-100 text-emerald-700 text-[8px] uppercase px-1 py-0.2 rounded font-bold shrink-0" title="Tersimpan di Buku Telepon">Kontak</span>
                                                    @endif
                                                </span>

                                                <div class="flex gap-1 items-center shrink-0 ml-1">
                                                    @if($t->has_new_message)
                                                        <span class="bg-blue-600 text-white text-[9px] font-extrabold px-1.5 py-0.5 rounded-full shadow-xs">BARU</span>
                                                    @endif

                                                    @if($t->status === 'closed')
                                                        <span class="bg-gray-200 text-gray-600 text-[9px] font-bold px-1.5 py-0.5 rounded-full">Selesai</span>
                                                    @elseif(is_null($t->agent_id))
                                                        <span class="bg-rose-100 text-rose-700 text-[9px] font-bold px-1.5 py-0.5 rounded-full">Antrean</span>
                                                    @else
                                                        <span class="bg-amber-100 text-amber-800 text-[9px] font-bold px-1.5 py-0.5 rounded-full">Diproses</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-between text-xs text-gray-500 font-mono">
                                                <span class="truncate">+{{ $t->number }}</span>
                                                <span class="text-[10px] text-gray-400 font-sans">{{ $t->updated_at->diffForHumans(null, true, true) }}</span>
                                            </div>

                                            <div class="text-[11px] text-gray-400 mt-1 flex items-center justify-between gap-1">
                                                <span class="truncate">Tiket: <strong class="text-indigo-600 font-mono">{{ $t->ticket_number }}</strong></span>
                                                <span class="{{ $tagClass }} border px-1.5 py-0.2 rounded text-[9px] uppercase font-bold shrink-0">{{ $t->tag ?? 'Umum' }}</span>
                                            </div>

                                            @if($t->agent)
                                                <div class="text-[10px] text-gray-400 mt-0.5 truncate">
                                                    Operator: <span class="text-gray-600 font-medium">{{ $t->agent->name }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="p-8 flex flex-col items-center justify-center text-center text-gray-400">
                                    @if($devices->isEmpty())
                                        <svg class="w-10 h-10 text-amber-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        @if(auth()->user()->isAgent())
                                            <p class="text-sm font-semibold text-gray-700">Belum Ada Akses Device</p>
                                            <p class="text-xs text-gray-500 mt-1 max-w-xs">Akun Anda belum memiliki akses ke device WhatsApp yang terhubung. Hubungi Admin Anda melalui menu Edit Agen.</p>
                                        @else
                                            <p class="text-sm font-semibold text-gray-700">Belum Ada Device Terhubung</p>
                                            <p class="text-xs text-gray-500 mt-1 max-w-xs">Tidak ada device WhatsApp yang terhubung saat ini. Silakan hubungkan device Anda di menu Device.</p>
                                        @endif
                                    @else
                                        <span class="text-sm">Tidak ada riwayat tiket aktif.</span>
                                    @endif
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- ======================================================== --}}
                    {{-- AREA RUANG CHAT (KANAN)                                   --}}
                    {{-- ======================================================== --}}
                    <div class="flex-1 flex flex-col bg-[#efeae2] overflow-hidden">
                        @if($activeTicket)
                            {{-- Header Ruang Chat Aktif --}}
                            <div class="bg-white p-3.5 sm:p-4 border-b border-gray-200 flex justify-between items-center shadow-xs z-10">
                                <div class="flex items-center gap-3 min-w-0">
                                    {{-- Avatar Kontak Header --}}
                                    <div class="relative shrink-0 cursor-pointer" onclick="if('{{ $activeTicket->avatar_url }}') openImageModal('{{ $activeTicket->avatar_url }}')" title="Klik untuk memperbesar foto profil">
                                        @if(!empty($activeTicket->avatar_url))
                                            <img src="{{ $activeTicket->avatar_url }}" 
                                                 alt="{{ $contactName }}" 
                                                 class="w-12 h-12 rounded-full object-cover border-2 border-emerald-400 shadow-sm"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 font-bold text-sm uppercase items-center justify-center border-2 border-emerald-300 shadow-sm" style="display: none;">
                                                {{ strtoupper(substr($contactName, 0, 2)) }}
                                            </div>
                                        @else
                                            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm uppercase border-2 border-emerald-300 shadow-sm">
                                                {{ strtoupper(substr($contactName, 0, 2)) }}
                                            </div>
                                        @endif
                                        <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></span>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h2 class="text-base sm:text-lg font-bold text-gray-900 truncate">
                                                {{ $contactName }}
                                            </h2>

                                            @php
                                                $isContact = $contacts->contains('number', $activeTicket->number);
                                            @endphp

                                            @if($isContact)
                                                <span class="bg-emerald-100 text-emerald-700 border border-emerald-300 text-[10px] uppercase font-bold px-2 py-0.5 rounded-full flex items-center gap-1 shadow-xs">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    Kontak
                                                </span>
                                            @else
                                                <button onclick="openAddContactModal('{{ $activeTicket->number }}', '{{ $activeTicket->customer_name }}')" class="bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 hover:text-indigo-600 text-[10px] uppercase font-bold px-2 py-0.5 rounded-full shadow-xs transition flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                    Tambah Kontak
                                                </button>
                                            @endif

                                            @php
                                                $activeTag = strtolower($activeTicket->tag ?? 'umum');
                                                $activeTagClass = $tagColors[$activeTag] ?? $tagColors['umum'];
                                            @endphp
                                            <span class="{{ $activeTagClass }} border text-[10px] uppercase font-bold px-2 py-0.5 rounded-full shadow-xs">
                                                {{ $activeTicket->tag ?? 'Umum' }}
                                            </span>
                                        </div>

                                        <p class="text-xs text-gray-500 font-mono mt-0.5">
                                            +{{ $activeTicket->number }} • Tiket: <span class="text-indigo-600 font-bold">{{ $activeTicket->ticket_number }}</span>
                                            @if($activeTicket->device)
                                                <span class="text-gray-400 font-sans">via <strong>{{ $activeTicket->device->name }}</strong></span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                {{-- Aksi Kontrol Tiket di Header --}}
                                <div class="flex gap-2 items-center flex-wrap justify-end">
                                    @if($activeTicket->status === 'open')
                                        {{-- 1. Transfer Tiket --}}
                                        @if(is_null($activeTicket->agent_id) || auth()->user()->isSuperadmin() || auth()->id() === $activeTicket->agent_id)
                                            <form action="{{ route('messages.transfer', $activeTicket->id) }}" method="POST" class="flex items-center">
                                                @csrf
                                                <select name="agent_id" onchange="this.form.submit()" class="text-xs border-gray-300 rounded-lg shadow-xs py-1.5 pl-2.5 pr-7 focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                                                    <option value="">{{ $activeTicket->agent ? 'Alihkan Tiket' : 'Tugaskan Agen' }}</option>
                                                    @foreach($agents as $agent)
                                                        @if($agent->id !== $activeTicket->agent_id)
                                                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </form>
                                        @endif

                                        {{-- 2. Ubah Tag Tiket --}}
                                        <form action="{{ route('messages.update_tag', $activeTicket->id) }}" method="POST" class="flex items-center">
                                            @csrf
                                            <select name="tag" onchange="this.form.submit()" class="text-xs border-gray-300 rounded-lg shadow-xs py-1.5 pl-2.5 pr-7 focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                                                <option value="">Ubah Tag...</option>
                                                @foreach(array_keys($tagColors) as $tName)
                                                    <option value="{{ $tName }}" {{ strtolower($activeTicket->tag ?? '') === $tName ? 'selected' : '' }}>
                                                        Tag: {{ ucfirst($tName) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>

                                        {{-- 3. Tombol Selesai (Tutup Tiket) --}}
                                        @if($activeTicket->agent_id === auth()->id() || auth()->user()->isSuperadmin())
                                            <form action="{{ route('messages.close', $activeTicket->id) }}" method="POST" onsubmit="return confirm('Tandai tiket ini selesai dan kirim pesan penutup ke pelanggan?');">
                                                @csrf
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3.5 py-1.5 rounded-lg transition shadow-xs flex items-center gap-1">
                                                    ✓ Selesai
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="bg-gray-100 text-gray-500 border border-gray-300 text-xs font-semibold px-3 py-1.5 rounded-full shadow-inner">
                                            🔒 Tiket Selesai
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Area Pesan Obrolan (Bubble Chat) --}}
                            <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4 flex flex-col" id="chat-messages">
                                @foreach($messages as $msg)
                                    <div class="flex w-full {{ $msg->direction == 'out' ? 'justify-end' : 'justify-start' }} items-end gap-2">
                                        {{-- Avatar Pelanggan pada Pesan Masuk --}}
                                        @if($msg->direction == 'in')
                                            <div class="w-7 h-7 rounded-full shrink-0 mb-1 overflow-hidden shadow-xs">
                                                @if(!empty($activeTicket->avatar_url))
                                                    <img src="{{ $activeTicket->avatar_url }}" alt="{{ $contactName }}" class="w-7 h-7 rounded-full object-cover border border-gray-200" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px] uppercase border border-emerald-300" style="display: none;">
                                                        {{ strtoupper(substr($contactName, 0, 2)) }}
                                                    </div>
                                                @else
                                                    <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px] uppercase border border-emerald-300">
                                                        {{ strtoupper(substr($contactName, 0, 2)) }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- Bubble Pesan --}}
                                        <div class="max-w-[78%] rounded-2xl p-3.5 shadow-xs relative {{ $msg->direction == 'out' ? 'bg-[#dcf8c6] text-gray-900 rounded-br-xs' : 'bg-white text-gray-900 rounded-bl-xs' }}">

                                            {{-- Kutipan Pesan (Jika Membalas Pesan) --}}
                                            @if($msg->quoted_message)
                                                <div class="mb-2 p-2 rounded-lg bg-black bg-opacity-5 border-l-4 {{ $msg->direction == 'out' ? 'border-emerald-600' : 'border-indigo-500' }} text-xs text-gray-600">
                                                    <span class="block font-bold {{ $msg->direction == 'out' ? 'text-emerald-700' : 'text-indigo-600' }} mb-0.5">Membalas pesan:</span>
                                                    <p class="whitespace-pre-wrap line-clamp-3 opacity-80">{{ $msg->quoted_message }}</p>
                                                </div>
                                            @endif

                                            {{-- Lampiran Media (Gambar / Dokumen) --}}
                                            @if($msg->media_url)
                                                @php
                                                    $isLocal = str_starts_with($msg->media_url, 'received_media/') || str_starts_with($msg->media_url, 'sent_media/');

                                                    if ($isLocal) {
                                                        $fullMediaUrl = asset('storage/' . $msg->media_url);
                                                        $extension = strtolower(pathinfo($msg->media_url, PATHINFO_EXTENSION));
                                                    } else {
                                                        if (str_starts_with($msg->media_url, 'http://') || str_starts_with($msg->media_url, 'https://')) {
                                                            $fullMediaUrl = $msg->media_url;
                                                            $cleanPath = parse_url($msg->media_url, PHP_URL_PATH);
                                                        } else {
                                                            $gowaBaseUrl = env('GOWA_API_URL', 'http://localhost:5271');
                                                            $cleanPath = str_replace('\\', '/', $msg->media_url);
                                                            $fullMediaUrl = rtrim($gowaBaseUrl, '/') . '/' . ltrim($cleanPath, '/');
                                                        }
                                                        $extension = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));
                                                    }

                                                    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'];
                                                    $isImage = in_array($extension, $imageExtensions);
                                                @endphp

                                                <div class="mb-2">
                                                    @if($isImage)
                                                        <a href="javascript:void(0)" onclick="openImageModal('{{ $fullMediaUrl }}')" title="Klik untuk memperbesar gambar">
                                                            <img src="{{ $fullMediaUrl }}" class="rounded-xl max-h-64 object-cover cursor-pointer hover:opacity-90 transition border border-gray-200 shadow-xs" alt="Gambar Lampiran" onerror="this.onerror=null; this.src='https://placehold.co/300x200?text=Gambar+Gagal+Dimuat';">
                                                        </a>
                                                    @else
                                                        <a href="{{ $fullMediaUrl }}" target="_blank" class="flex items-center gap-3 p-3 bg-black bg-opacity-5 hover:bg-opacity-10 transition rounded-xl border border-gray-200 max-w-xs">
                                                            <div class="bg-indigo-500 text-white p-2 rounded-lg flex-shrink-0">
                                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                                            </div>
                                                            <div class="flex flex-col overflow-hidden">
                                                                <span class="text-sm font-semibold text-gray-800 truncate">Lampiran Dokumen</span>
                                                                <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">{{ $extension }} FILE</span>
                                                            </div>
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- Teks Pesan --}}
                                            @if($msg->message)
                                                <p class="text-sm whitespace-pre-wrap leading-relaxed">{{ $msg->message }}</p>
                                            @endif

                                            <div class="flex items-center justify-end gap-1 mt-1 opacity-70">
                                                <span class="text-[10px] text-gray-500">{{ $msg->created_at->format('d M, H:i') }}</span>
                                                @if($msg->direction == 'out')
                                                    <svg class="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Footer Input Balasan Agen --}}
                            @if($activeTicket->status === 'open')

                                @if(is_null($activeTicket->agent_id))
                                    <div class="p-6 sm:p-8 bg-gray-50 border-t border-gray-200 flex flex-col items-center justify-center text-center">
                                        <div class="bg-indigo-100 text-indigo-600 p-3 rounded-full mb-3 shadow-xs">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                                        </div>
                                        <h3 class="text-base font-bold text-gray-800 mb-1">Tiket Dalam Antrean</h3>
                                        <p class="text-xs text-gray-500 mb-4 max-w-md">Anda sedang dalam mode pratinjau (Preview). Jika Anda siap melayani pesan ini, silakan ambil tiketnya.</p>

                                        <form action="{{ route('messages.start', $activeTicket->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-xl shadow transition flex items-center gap-2 text-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Mulai Layanan
                                            </button>
                                        </form>
                                    </div>

                                @elseif($activeTicket->agent_id !== auth()->id() && !auth()->user()->isSuperadmin())
                                    <div class="p-4 bg-amber-50 border-t border-amber-200 text-center">
                                        <span class="text-amber-700 text-sm font-semibold flex items-center justify-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            Tiket ini sedang dilayani oleh agen lain.
                                        </span>
                                    </div>

                                @else
                                    <div class="p-4 bg-gray-50 border-t border-gray-200">
                                        <form action="{{ route('messages.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2">
                                            @csrf
                                            <input type="hidden" name="device_id" value="{{ $activeTicket->device_id }}">
                                            <input type="hidden" name="number" value="{{ $activeTicket->number }}">

                                            <div id="file-preview-container" class="hidden px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-xl text-xs flex justify-between items-center text-indigo-700 font-medium">
                                                <div class="flex items-center gap-2 overflow-hidden">
                                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                    <span id="file-name-display" class="truncate">nama-file.jpg</span>
                                                </div>
                                                <button type="button" onclick="removeFile()" class="text-red-500 hover:text-red-700 font-bold ml-2">✕ Hapus</button>
                                            </div>

                                            <div class="flex gap-2 items-end">
                                                <label class="cursor-pointer bg-white border border-gray-300 hover:bg-gray-100 text-gray-500 rounded-full p-3 shadow-xs transition flex items-center justify-center flex-shrink-0 w-12 h-12" title="Lampirkan Dokumen/Gambar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                    <input type="file" name="file" id="file-upload" class="hidden" onchange="showFilePreview(this)">
                                                </label>

                                                <textarea name="message" rows="1" class="flex-1 resize-none border-gray-300 rounded-full shadow-xs focus:border-indigo-500 focus:ring-indigo-500 py-3 px-5 text-sm" placeholder="Ketik pesan atau caption lampiran..."></textarea>

                                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-3 shadow-sm transition flex items-center justify-center flex-shrink-0 w-12 h-12">
                                                    <svg class="w-5 h-5 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif

                            @else
                                <div class="p-4 bg-gray-50 border-t border-gray-200 text-center">
                                    <span class="text-gray-500 text-sm font-medium">Sesi obrolan tiket ini telah ditutup.</span>
                                </div>
                            @endif

                        @else
                            {{-- Tampilan Kosong Saat Belum Memilih Tiket --}}
                            <div class="flex-1 flex flex-col items-center justify-center text-center px-4">
                                <div class="w-20 h-20 rounded-full bg-white shadow-sm flex items-center justify-center text-indigo-500 mb-4 border border-gray-200">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                </div>
                                <h2 class="text-xl font-bold text-gray-800">Ruang Obrolan Live Chat</h2>
                                <p class="text-gray-500 mt-1.5 max-w-md text-xs leading-relaxed">
                                    Pilih percakapan pelanggan di panel sebelah kiri untuk mulai melayani, atau klik tombol <strong>+ Pesan Baru</strong> untuk mengirimkan pesan ke nomor baru.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- MODAL PESAN BARU --}}
    <div id="newMessageModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('newMessageModal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-visible shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-100">
                <form action="{{ route('messages.store') }}" method="POST">
                    @csrf
                    <div class="bg-white px-5 pt-6 pb-4 sm:p-7 sm:pb-4 rounded-t-2xl">
                        <div class="flex justify-between items-center mb-4 border-b pb-3">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Kirim Pesan Baru
                            </h3>
                            <button type="button" onclick="document.getElementById('newMessageModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Pilih Device Pengirim</label>
                            <select name="device_id" class="w-full text-sm border-gray-300 rounded-xl shadow-xs focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3" required>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">
                                        {{ $device->name }} ({{ $device->phone_number ? '+' . $device->phone_number : 'Nomor belum diset' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <div class="flex justify-between items-end mb-1">
                                <label class="block text-xs font-bold text-gray-700 uppercase">Nomor Tujuan</label>
                                <select onchange="if(this.value) { let target = document.getElementById('target_numbers'); target.value = target.value ? target.value + ', ' + this.value : this.value; this.value = ''; }" class="text-xs border-gray-300 rounded-lg shadow-xs focus:ring-indigo-500 py-1 pl-2 pr-6">
                                    <option value="">+ Sisipkan dari Buku Telepon</option>
                                    @foreach($contacts as $contact)
                                        <option value="{{ $contact->number }}">{{ $contact->name }} ({{ $contact->number }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <textarea id="target_numbers" name="number" rows="2" class="w-full text-sm border-gray-300 rounded-xl shadow-xs focus:ring-indigo-500 focus:border-indigo-500 font-mono text-indigo-700 p-3" placeholder="Contoh: 081234567890, 628987654321" required></textarea>
                            <p class="text-[11px] text-gray-400 mt-1">Bisa memasukkan beberapa nomor sekaligus dipisahkan tanda koma.</p>
                        </div>

                        <div class="mb-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Isi Pesan</label>
                            <textarea name="message" rows="5" class="w-full text-sm border-gray-300 rounded-xl shadow-xs focus:ring-indigo-500 focus:border-indigo-500 p-3" placeholder="Ketik pesan Anda di sini..." required></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse rounded-b-2xl border-t border-gray-100 gap-2">
                        <button type="submit" class="w-full inline-flex justify-center rounded-xl shadow-sm px-5 py-2.5 bg-indigo-600 text-sm font-bold text-white hover:bg-indigo-700 sm:w-auto transition" onclick="this.innerHTML='Mengirim...';">
                            Kirim Pesan
                        </button>
                        <button type="button" class="mt-2 sm:mt-0 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-xs px-4 py-2.5 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:w-auto transition" onclick="document.getElementById('newMessageModal').classList.add('hidden')">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL LIGHTBOX GAMBAR --}}
    <div id="imageLightboxModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black bg-opacity-80 backdrop-blur-sm transition-opacity cursor-pointer" onclick="closeImageModal()"></div>
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10 p-2 bg-black bg-opacity-50 rounded-full">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <div class="relative z-10 max-w-4xl max-h-screen">
            <img id="lightboxImage" src="" class="max-w-full max-h-[90vh] object-contain rounded-2xl shadow-2xl border border-white/20" alt="Preview Gambar">
        </div>
    </div>

    {{-- MODAL TAMBAH KONTAK DARI CHAT --}}
    <div id="addContactModal" class="fixed inset-0 z-[70] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeAddContactModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-visible shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100">
                <form action="{{route('phonebook.store')}}" method="POST" id="formAddContact">
                    @csrf
                    <div class="bg-white p-6 sm:p-7 rounded-t-2xl">
                        <div class="flex justify-between items-center mb-4 border-b pb-3">
                            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                Simpan ke Buku Telepon
                            </h3>
                            <button type="button" onclick="closeAddContactModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nomor WhatsApp</label>
                                <input type="text" name="number" id="contactModalNumber" class="w-full text-sm border-gray-200 rounded-xl shadow-xs bg-gray-50 text-gray-700 font-mono py-2.5 px-3" readonly required>
                                <p class="text-[11px] text-gray-400 mt-1">Nomor sudah terisi otomatis dari obrolan pelanggan.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Lengkap</label>
                                <input type="text" name="name" id="contactModalName" class="w-full text-sm border-gray-300 rounded-xl shadow-xs focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3" placeholder="Contoh: Budi Santoso" required autofocus>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kategori (Opsional)</label>
                                <input type="text" name="category" placeholder="Contoh: Pelanggan, Mitra..." class="w-full text-sm border-gray-300 rounded-xl shadow-xs focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse rounded-b-2xl border-t border-gray-100 gap-2">
                        <button type="submit" class="w-full inline-flex justify-center rounded-xl shadow-sm px-5 py-2.5 bg-indigo-600 text-sm font-bold text-white hover:bg-indigo-700 sm:w-auto transition">
                            Simpan Kontak
                        </button>
                        <button type="button" class="mt-2 sm:mt-0 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-xs px-4 py-2.5 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:w-auto transition" onclick="closeAddContactModal()">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // --- PREVIEW FILE SEBELUM KIRIM ---
        function showFilePreview(input) {
            if (input.files && input.files[0]) {
                document.getElementById('file-name-display').textContent = input.files[0].name;
                document.getElementById('file-preview-container').classList.remove('hidden');
            }
        }

        function removeFile() {
            document.getElementById('file-upload').value = '';
            document.getElementById('file-preview-container').classList.add('hidden');
        }

        // --- LIGHTBOX PREVIEW GAMBAR ---
        function openImageModal(imageUrl) {
            let modal = document.getElementById('imageLightboxModal');
            let img = document.getElementById('lightboxImage');
            img.src = imageUrl;
            modal.classList.remove('hidden');
        }

        function closeImageModal() {
            let modal = document.getElementById('imageLightboxModal');
            modal.classList.add('hidden');
            setTimeout(() => { document.getElementById('lightboxImage').src = ''; }, 300);
        }

        // --- MODAL TAMBAH KONTAK ---
        function openAddContactModal(number, name) {
            document.getElementById('addContactModal').classList.remove('hidden');
            document.getElementById('contactModalNumber').value = number;

            let nameInput = document.getElementById('contactModalName');
            nameInput.value = name ? name : '';
            nameInput.focus();
        }

        function closeAddContactModal() {
            document.getElementById('addContactModal').classList.add('hidden');
        }

        // --- AUTO SCROLL & SILENT LIVE REFRESH POLLING ---
        document.addEventListener('DOMContentLoaded', function() {
            let chatBox = document.getElementById('chat-messages');
            if(chatBox) chatBox.scrollTop = chatBox.scrollHeight;

            setInterval(() => {
                fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.text())
                .then(html => {
                    let doc = new DOMParser().parseFromString(html, 'text/html');

                    // Update sidebar silently
                    let newSidebar = doc.getElementById('sidebar-list');
                    let oldSidebar = document.getElementById('sidebar-list');
                    if (newSidebar && oldSidebar) {
                        oldSidebar.innerHTML = newSidebar.innerHTML;
                    }

                    // Update chat room silently
                    let newChat = doc.getElementById('chat-messages');
                    let oldChat = document.getElementById('chat-messages');

                    if (newChat && oldChat) {
                        if (newChat.children.length !== oldChat.children.length) {
                            oldChat.innerHTML = newChat.innerHTML;
                            oldChat.scrollTop = oldChat.scrollHeight;
                        }
                    }
                })
                .catch(err => console.error('Gagal memuat pembaruan pesan:', err));
            }, 4000);
        });
    </script>
</x-app-layout>
