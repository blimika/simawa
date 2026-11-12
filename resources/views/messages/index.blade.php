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
        $currentTab = $currentTab ?? request('tab', 'all');
        $counts = $counts ?? ['all' => 0, 'antrean' => 0, 'diproses' => 0, 'selesai' => 0];
    @endphp

    {{-- KONTEN UTAMA: 1 HALAMAN PAS (FULL VIEWPORT FIT, TANPA SCROLLBAR HALAMAN) --}}
    <div class="h-[calc(100vh-65px)] max-h-[calc(100vh-65px)] overflow-hidden bg-gray-100 p-2 sm:p-3 flex flex-col">
        @if(session('success'))
            <div class="mb-2 bg-emerald-600 text-white text-xs px-4 py-2 rounded-xl flex items-center justify-between shadow-sm shrink-0">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </span>
                <button type="button" onclick="this.parentElement.remove()" class="text-white hover:text-emerald-100 font-bold ml-2">✕</button>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-2 bg-rose-600 text-white text-xs px-4 py-2 rounded-xl flex items-center justify-between shadow-sm shrink-0">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ session('error') }}
                </span>
                <button type="button" onclick="this.parentElement.remove()" class="text-white hover:text-rose-100 font-bold ml-2">✕</button>
            </div>
        @endif

        <div class="flex-1 min-h-0 bg-white rounded-2xl shadow-sm border border-gray-200/80 overflow-hidden flex h-full">

            {{-- ======================================================== --}}
            {{-- SIDEBAR KIRI: ANTREAN PERCAKAPAN & TIKET                 --}}
            {{-- ======================================================== --}}
            <div class="w-full sm:w-80 md:w-96 lg:w-[380px] xl:w-[410px] shrink-0 bg-white border-r border-gray-200/80 {{ $activeTicket ? 'hidden sm:flex' : 'flex' }} flex-col h-full min-h-0">

                {{-- Header Sidebar & Pencarian & Filter Tabs --}}
                <div class="p-3.5 border-b border-gray-200/80 bg-white shadow-sm z-10 flex flex-col gap-2.5 shrink-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            </div>
                            <div>
                                <h1 class="text-base font-bold text-gray-900 leading-tight">Live Chat</h1>
                                <p class="text-[11px] text-gray-400">WhatsApp Pelayanan</p>
                            </div>
                        </div>
                        <button type="button" onclick="document.getElementById('newMessageModal').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-1.5 px-3 rounded-xl text-xs transition duration-150 shadow-sm flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span>Pesan Baru</span>
                        </button>
                    </div>

                    {{-- Form Pencarian --}}
                    <form action="{{ route('messages.index') }}" method="GET" class="relative">
                        @if(request('ticket_id'))
                            <input type="hidden" name="ticket_id" value="{{ request('ticket_id') }}">
                        @endif
                        <input type="hidden" name="tab" value="{{ $currentTab }}">

                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, nomor, atau tiket..." class="w-full text-xs border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 pl-8 pr-7 py-2 transition-all">
                        <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>

                        @if(request('search'))
                            <a href="{{ route('messages.index', array_filter(['ticket_id' => request('ticket_id'), 'tab' => $currentTab])) }}" class="absolute right-2.5 top-2.5 text-gray-400 hover:text-red-500" title="Hapus pencarian">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </a>
                        @endif
                    </form>

                    {{-- Tombol Filter Status: Semua, Antrean, Diproses, Selesai --}}
                    <div id="sidebar-tabs" class="flex items-center gap-1.5 overflow-x-auto no-scrollbar text-xs pt-0.5">
                        <a href="{{ route('messages.index', array_filter(['tab' => 'all', 'search' => request('search'), 'ticket_id' => request('ticket_id')])) }}" 
                           class="px-2.5 py-1 rounded-lg font-semibold transition flex items-center gap-1 shrink-0 {{ $currentTab === 'all' ? 'bg-gray-900 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            <span>Semua</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold {{ $currentTab === 'all' ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700' }}">
                                {{ $counts['all'] }}
                            </span>
                        </a>

                        <a href="{{ route('messages.index', array_filter(['tab' => 'antrean', 'search' => request('search'), 'ticket_id' => request('ticket_id')])) }}" 
                           class="px-2.5 py-1 rounded-lg font-semibold transition flex items-center gap-1 shrink-0 {{ $currentTab === 'antrean' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200' }}">
                            <span>Antrean</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold {{ $currentTab === 'antrean' ? 'bg-white/20 text-white' : 'bg-rose-200 text-rose-800' }}">
                                {{ $counts['antrean'] }}
                            </span>
                        </a>

                        <a href="{{ route('messages.index', array_filter(['tab' => 'diproses', 'search' => request('search'), 'ticket_id' => request('ticket_id')])) }}" 
                           class="px-2.5 py-1 rounded-lg font-semibold transition flex items-center gap-1 shrink-0 {{ $currentTab === 'diproses' ? 'bg-blue-600 text-white shadow-sm' : 'bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200' }}">
                            <span>Diproses</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold {{ $currentTab === 'diproses' ? 'bg-white/20 text-white' : 'bg-blue-200 text-blue-800' }}">
                                {{ $counts['diproses'] }}
                            </span>
                        </a>

                        <a href="{{ route('messages.index', array_filter(['tab' => 'selesai', 'search' => request('search'), 'ticket_id' => request('ticket_id')])) }}" 
                           class="px-2.5 py-1 rounded-lg font-semibold transition flex items-center gap-1 shrink-0 {{ $currentTab === 'selesai' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' }}">
                            <span>Selesai</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded-full font-bold {{ $currentTab === 'selesai' ? 'bg-white/20 text-white' : 'bg-emerald-200 text-emerald-800' }}">
                                {{ $counts['selesai'] }}
                            </span>
                        </a>
                    </div>
                </div>

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

                        <a href="{{ route('messages.index', array_filter(['ticket_id' => $t->id, 'tab' => $currentTab, 'search' => request('search')])) }}" 
                           class="block p-3.5 hover:bg-gray-100/80 transition {{ request('ticket_id') == $t->id ? 'bg-indigo-50/70 border-l-4 border-indigo-500 shadow-sm' : '' }} {{ $t->status === 'closed' ? 'bg-emerald-50/20' : '' }}">
                            <div class="flex items-start gap-3">
                                {{-- Avatar Kontak di Sidebar --}}
                                <div class="relative shrink-0 mt-0.5">
                                    @if(!empty($avatarUrl))
                                        <img src="{{ $avatarUrl }}" 
                                             alt="{{ $displayName }}" 
                                             class="w-11 h-11 rounded-full object-cover border border-gray-200 shadow-sm" 
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="w-11 h-11 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs uppercase items-center justify-center border border-emerald-200 shadow-sm" style="display: none;">
                                            {{ strtoupper(substr($displayName, 0, 2)) }}
                                        </div>
                                    @else
                                        <div class="w-11 h-11 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs uppercase border border-emerald-200 shadow-sm">
                                            {{ strtoupper(substr($displayName, 0, 2)) }}
                                        </div>
                                    @endif

                                    {{-- Indikator Pesan Baru --}}
                                    @if($t->has_new_message)
                                        <span class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 bg-indigo-600 border-2 border-white rounded-full animate-ping"></span>
                                        <span class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 bg-indigo-600 border-2 border-white rounded-full"></span>
                                    @endif
                                </div>

                                {{-- Detail Info Tiket --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start mb-0.5">
                                        <span class="font-bold text-sm truncate {{ $t->has_new_message ? 'text-indigo-950 font-black' : 'text-gray-900' }} flex items-center gap-1">
                                            <span class="truncate">{{ $displayName }}</span>
                                            @if($contact)
                                                <span class="bg-emerald-100 text-emerald-700 text-[8px] uppercase px-1 py-0.5 rounded font-bold shrink-0" title="Tersimpan di Buku Telepon">Kontak</span>
                                            @endif
                                        </span>

                                        <div class="flex gap-1 items-center shrink-0 ml-1">
                                            @if($t->has_new_message)
                                                <span class="bg-indigo-600 text-white text-[9px] font-extrabold px-1.5 py-0.5 rounded-full shadow-sm">BARU</span>
                                            @endif

                                            {{-- Label Status Tiket: Selesai (HIJAU!), Antrean, Diproses --}}
                                            @if($t->status === 'closed')
                                                <span class="bg-emerald-100 text-emerald-700 border border-emerald-300 text-[9px] font-bold px-2 py-0.5 rounded-full flex items-center gap-0.5 shadow-sm">
                                                    <svg class="w-2.5 h-2.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                    Selesai
                                                </span>
                                            @elseif(is_null($t->agent_id))
                                                <span class="bg-rose-100 text-rose-700 border border-rose-300 text-[9px] font-bold px-2 py-0.5 rounded-full shadow-sm">Antrean</span>
                                            @else
                                                <span class="bg-blue-100 text-blue-700 border border-blue-300 text-[9px] font-bold px-2 py-0.5 rounded-full shadow-sm">Diproses</span>
                                            @endif
                                        </div>
                                    </div>

                                            <div class="flex items-center justify-between text-xs text-gray-500 font-mono">
                                                <span class="truncate">+{{ $t->number }}</span>
                                                <span class="text-[10px] text-gray-400 font-sans" title="{{ $t->updated_at->format('d M Y, H:i') }}">{{ $t->updated_at->diffForHumans(null, true, true) }}</span>
                                            </div>

                                            <div class="text-[11px] text-gray-400 mt-1 flex items-center justify-between gap-1">
                                                <span class="truncate">Tiket: <strong class="text-indigo-600 font-mono">{{ $t->ticket_number }}</strong></span>
                                                <span class="{{ $tagClass }} border px-1.5 py-0.5 rounded text-[9px] uppercase font-bold shrink-0">{{ $t->tag ?? 'Umum' }}</span>
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
                    <div class="flex-1 min-w-0 min-h-0 {{ $activeTicket ? 'flex' : 'hidden sm:flex' }} flex-col bg-[#efeae2] overflow-hidden">
                        @if($activeTicket)
                            {{-- Header Ruang Chat Aktif --}}
                            <div class="bg-white p-3.5 sm:p-4 border-b border-gray-200 flex justify-between items-center shadow-sm z-10 shrink-0 gap-2">
                                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                                    {{-- Tombol Kembali (Khusus Mobile) --}}
                                    <a href="{{ route('messages.index', array_filter(['tab' => $currentTab, 'search' => request('search')])) }}"
                                       class="sm:hidden p-1.5 -ml-1 rounded-lg text-gray-500 hover:text-indigo-600 hover:bg-gray-100 shrink-0"
                                       title="Kembali ke daftar chat">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                    </a>

                                    {{-- Avatar Kontak Header --}}
                                    <div class="relative shrink-0 cursor-pointer" data-avatar="{{ $activeTicket->avatar_url }}" onclick="if(this.dataset.avatar) openImageModal(this.dataset.avatar)" title="Klik untuk memperbesar foto profil">
                                        @if(!empty($activeTicket->avatar_url))
                                            <img src="{{ $activeTicket->avatar_url }}" 
                                                 alt="{{ $contactName }}" 
                                                 class="w-11 h-11 sm:w-12 sm:h-12 rounded-full object-cover border-2 border-emerald-400 shadow-sm"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-emerald-100 text-emerald-700 font-bold text-sm uppercase items-center justify-center border-2 border-emerald-300 shadow-sm" style="display: none;">
                                                {{ strtoupper(substr($contactName, 0, 2)) }}
                                            </div>
                                        @else
                                            <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm uppercase border-2 border-emerald-300 shadow-sm">
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
                                                <span class="bg-emerald-100 text-emerald-700 border border-emerald-300 text-[10px] uppercase font-bold px-2 py-0.5 rounded-full flex items-center gap-1 shadow-sm">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    Kontak
                                                </span>
                                            @else
                                                <button type="button" data-number="{{ $activeTicket->number }}" data-name="{{ $activeTicket->customer_name }}" onclick="openAddContactModal(this.dataset.number, this.dataset.name)" class="bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 hover:text-indigo-600 text-[10px] uppercase font-bold px-2 py-0.5 rounded-full shadow-sm transition flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                    Tambah Kontak
                                                </button>
                                            @endif

                                            @php
                                                $activeTag = strtolower($activeTicket->tag ?? 'umum');
                                                $activeTagClass = $tagColors[$activeTag] ?? $tagColors['umum'];
                                            @endphp
                                            <span class="{{ $activeTagClass }} border text-[10px] uppercase font-bold px-2 py-0.5 rounded-full shadow-sm">
                                                {{ $activeTicket->tag ?? 'Umum' }}
                                            </span>
                                        </div>

                                        <p class="text-xs text-gray-500 font-mono mt-0.5">
                                            +{{ $activeTicket->number }} • Tiket: <span class="text-indigo-600 font-bold">{{ $activeTicket->ticket_number }}</span>
                                            @if($activeTicket->device)
                                                <span class="text-gray-400 font-sans">via <strong>{{ $activeTicket->device->name }}</strong></span>
                                            @endif
                                            @if($activeTicket->agent)
                                                <span class="text-gray-500 font-sans ml-1">• Agen: <strong class="text-indigo-700">{{ $activeTicket->agent->name }}</strong></span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                {{-- Aksi Kontrol Tiket di Header --}}
                                <div class="flex gap-2 items-center flex-wrap justify-end shrink-0">
                                    @if($activeTicket->status === 'open')
                                        {{-- 1. Transfer Tiket --}}
                                        @if(is_null($activeTicket->agent_id) || auth()->user()->isSuperadmin() || auth()->user()->isAdmin() || auth()->id() === $activeTicket->agent_id)
                                            <form action="{{ route('messages.transfer', $activeTicket->id) }}" method="POST" class="flex items-center">
                                                @csrf
                                                @php
                                                    $availableAgents = $agents->where('id', '!=', $activeTicket->agent_id);
                                                @endphp
                                                <select name="target_agent_id" 
                                                        onchange="if(this.value) { if(confirm('Alihkan tiket ini ke ' + this.options[this.selectedIndex].text + '?')) { this.form.submit(); } else { this.value = ''; } }" 
                                                        class="text-xs border-gray-300 rounded-lg shadow-sm py-1.5 pl-2.5 pr-7 focus:ring-indigo-500 focus:border-indigo-500 font-medium cursor-pointer"
                                                        {{ $availableAgents->isEmpty() ? 'disabled' : '' }}>
                                                    <option value="">
                                                        @if($availableAgents->isEmpty())
                                                            Tidak ada agen lain
                                                        @else
                                                            {{ $activeTicket->agent ? 'Alihkan Tiket...' : 'Tugaskan Agen...' }}
                                                        @endif
                                                    </option>
                                                    @foreach($availableAgents as $agent)
                                                        <option value="{{ $agent->id }}">{{ $agent->name }}{{ $agent->role === 'admin' ? ' (Admin)' : '' }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        @endif

                                        {{-- 2. Ubah Tag Tiket --}}
                                        <form action="{{ route('messages.update_tag', $activeTicket->id) }}" method="POST" class="flex items-center">
                                            @csrf
                                            <select name="tag" onchange="this.form.submit()" class="text-xs border-gray-300 rounded-lg shadow-sm py-1.5 pl-2.5 pr-7 focus:ring-indigo-500 focus:border-indigo-500 font-medium">
                                                <option value="">Ubah Tag...</option>
                                                @foreach(array_keys($tagColors) as $tName)
                                                    <option value="{{ $tName }}" {{ strtolower($activeTicket->tag ?? '') === $tName ? 'selected' : '' }}>
                                                        Tag: {{ ucfirst($tName) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </form>

                                        {{-- 3. Tombol Selesai (Tutup Tiket) --}}
                                        @if($activeTicket->agent_id === auth()->id() || auth()->user()->isSuperadmin() || auth()->user()->isAdmin())
                                            <form action="{{ route('messages.close', $activeTicket->id) }}" method="POST" onsubmit="return confirm('Tandai tiket ini selesai dan kirim pesan penutup ke pelanggan?');">
                                                @csrf
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-3.5 py-1.5 rounded-lg transition shadow-sm flex items-center gap-1">
                                                    ✓ Selesai
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-bold px-3 py-1.5 rounded-full shadow-sm flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                            Tiket Selesai
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @php
                                $canReply = $activeTicket->status === 'open'
                                    && !is_null($activeTicket->agent_id)
                                    && ($activeTicket->agent_id === auth()->id() || auth()->user()->isSuperadmin() || auth()->user()->isAdmin());
                            @endphp

                            {{-- Area Pesan Obrolan (Bubble Chat) --}}
                            <div class="flex-1 min-h-0 overflow-y-auto p-4 sm:p-6 space-y-4 flex flex-col" id="chat-messages">
                                @foreach($messages as $msg)
                                    @php
                                        $replySenderLabel = $contactName;
                                        if ($msg->direction === 'out') {
                                            if ($msg->user_id === auth()->id()) {
                                                $replySenderLabel = 'Anda (' . auth()->user()->name . ')';
                                            } elseif ($msg->user) {
                                                $replySenderLabel = $msg->user->name;
                                            } else {
                                                $replySenderLabel = 'Anda / WhatsApp';
                                            }
                                        }

                                        $replyPreviewText = trim((string) $msg->message);
                                        if (empty($replyPreviewText) && $msg->media_url) {
                                            $rExt = strtolower(pathinfo(parse_url($msg->media_url, PHP_URL_PATH) ?: $msg->media_url, PATHINFO_EXTENSION));
                                            $rIsImg = in_array($rExt, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif', 'svg', 'bmp']);
                                            $replyPreviewText = $rIsImg ? '📷 Foto' : '📄 Dokumen (' . basename($msg->media_url) . ')';
                                        } elseif (!empty($replyPreviewText) && $msg->media_url) {
                                            $replyPreviewText = '📎 ' . $replyPreviewText;
                                        }
                                        $replyPreviewSingleLine = \Illuminate\Support\Str::limit(str_replace(["\r", "\n"], ' ', $replyPreviewText), 110);
                                    @endphp

                                    <div id="msg-{{ $msg->id }}" class="flex w-full {{ $msg->direction == 'out' ? 'justify-end' : 'justify-start' }} items-end gap-1.5 group">
                                        {{-- Avatar Pelanggan pada Pesan Masuk --}}
                                        @if($msg->direction == 'in')
                                            <div class="w-7 h-7 rounded-full shrink-0 mb-1 overflow-hidden shadow-sm">
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

                                        {{-- Tombol Balas Pesan Sendiri (Sisi Kiri Bubble Keluar) --}}
                                        @if($canReply && $msg->direction == 'out')
                                            <button type="button"
                                                    onclick="selectReply(this)"
                                                    data-msg-id="{{ $msg->id }}"
                                                    data-msg-sender="{{ $replySenderLabel }}"
                                                    data-msg-preview="{{ $replyPreviewSingleLine }}"
                                                    class="opacity-60 sm:opacity-0 group-hover:opacity-100 focus:opacity-100 mb-2 w-7 h-7 rounded-full bg-white hover:bg-indigo-50 text-gray-500 hover:text-indigo-600 shadow-sm border border-gray-200 flex items-center justify-center transition shrink-0"
                                                    title="Balas pesan ini">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                            </button>
                                        @endif

                                        {{-- Bubble Pesan --}}
                                        <div class="message-bubble max-w-[78%] rounded-2xl p-3.5 shadow-sm relative transition-all duration-300 {{ $msg->direction == 'out' ? 'bg-[#dcf8c6] text-gray-900 rounded-br-sm' : 'bg-white text-gray-900 rounded-bl-sm' }}">

                                            {{-- Kutipan Pesan (Jika Membalas Pesan) --}}
                                            @if($msg->quoted_message)
                                                @php
                                                    $quotedSenderName = 'Membalas pesan';
                                                    if ($msg->replyTo) {
                                                        if ($msg->replyTo->direction === 'out') {
                                                            $quotedSenderName = $msg->replyTo->user_id === auth()->id()
                                                                ? 'Anda (' . auth()->user()->name . ')'
                                                                : ($msg->replyTo->user ? $msg->replyTo->user->name : 'Anda / WhatsApp');
                                                        } else {
                                                            $quotedSenderName = $contactName;
                                                        }
                                                    }
                                                @endphp
                                                <div @if($msg->reply_to_id) onclick="scrollToMessage({{ $msg->reply_to_id }})" title="Klik untuk melihat pesan asli" @endif
                                                     class="mb-2 p-2 rounded-lg bg-black/5 border-l-4 {{ $msg->direction == 'out' ? 'border-emerald-600' : 'border-indigo-500' }} text-xs text-gray-600 {{ $msg->reply_to_id ? 'cursor-pointer hover:bg-black/10 transition' : '' }}">
                                                    <span class="block font-bold {{ $msg->direction == 'out' ? 'text-emerald-700' : 'text-indigo-600' }} mb-0.5">{{ $quotedSenderName }}</span>
                                                    <p class="whitespace-pre-wrap line-clamp-3 opacity-85">{{ $msg->quoted_message }}</p>
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
                                                            $gowaBaseUrl = config('services.gowa.url', env('GOWA_API_URL', 'http://localhost:5271'));
                                                            $cleanPath = str_replace('\\', '/', $msg->media_url);
                                                            $fullMediaUrl = rtrim($gowaBaseUrl, '/') . '/' . ltrim($cleanPath, '/');
                                                        }
                                                        $extension = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));
                                                    }

                                                    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif', 'svg', 'bmp'];
                                                    $isImage = in_array($extension, $imageExtensions);
                                                    $downloadUrl = route('messages.media.download', $msg->id);
                                                    $viewUrl = route('messages.media.view', $msg->id);
                                                @endphp

                                                <div class="mb-2">
                                                    @if($isImage)
                                                        <div class="relative group/img inline-block max-w-full">
                                                            <a href="javascript:void(0)" onclick="openImageModal('{{ $fullMediaUrl }}', '{{ $downloadUrl }}')" title="Klik untuk memperbesar gambar">
                                                                <img src="{{ $fullMediaUrl }}" 
                                                                     class="rounded-xl max-h-64 object-cover cursor-pointer hover:opacity-95 transition border border-gray-200 shadow-sm" 
                                                                     alt="Gambar Lampiran" 
                                                                     onerror="if (!this.dataset.retried) { this.dataset.retried = '1'; this.src='{{ $viewUrl }}'; } else { this.onerror=null; this.src='https://placehold.co/300x200?text=Gambar+Gagal+Dimuat'; }">
                                                            </a>
                                                            <div class="mt-1 flex items-center justify-between gap-2">
                                                                <a href="{{ $downloadUrl }}" class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-600 hover:text-indigo-600 transition bg-black/5 hover:bg-black/10 px-2.5 py-1 rounded-md" title="Simpan gambar ke lokal">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                                    <span>Unduh Gambar</span>
                                                                </a>
                                                                <span class="text-[10px] text-gray-400 uppercase font-mono">{{ $extension }}</span>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="flex items-center justify-between gap-3 p-3 bg-black/5 hover:bg-black/10 transition rounded-xl border border-gray-200 max-w-sm">
                                                            <div class="flex items-center gap-3 overflow-hidden">
                                                                <div class="p-2.5 rounded-lg flex-shrink-0 shadow-sm {{ $extension === 'pdf' ? 'bg-rose-500 text-white' : 'bg-indigo-500 text-white' }}">
                                                                    @if($extension === 'pdf')
                                                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9.5 8.5h-1v-2h1c.55 0 1 .45 1 1s-.45 1-1 1zm5.5 2h-1.5v-1h1.5c.28 0 .5-.22.5-.5s-.22-.5-.5-.5H15v-1h1.5c.55 0 1 .45 1 1v1c0 .55-.45 1-1 1zm-3-1c0 .55-.45 1-1 1h-1.5v-4H11c.55 0 1 .45 1 1v2z"/></svg>
                                                                    @else
                                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                                                    @endif
                                                                </div>
                                                                <div class="flex flex-col overflow-hidden text-left">
                                                                    <span class="text-xs font-semibold text-gray-800 truncate" title="{{ basename($msg->media_url) }}">
                                                                        {{ basename($msg->media_url) }}
                                                                    </span>
                                                                    <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">{{ $extension ? strtoupper($extension) . ' FILE' : 'DOKUMEN' }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                                                <a href="{{ $viewUrl }}" target="_blank" class="p-2 text-gray-600 hover:text-indigo-600 hover:bg-white rounded-lg transition" title="Lihat Pratinjau Dokumen">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                                </a>
                                                                <a href="{{ $downloadUrl }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition shadow-sm text-xs font-semibold" title="Unduh dan simpan ke lokal">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                                                    <span>Unduh</span>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- Teks Pesan --}}
                                            @if($msg->message)
                                                <p class="text-sm whitespace-pre-wrap leading-relaxed break-words">{{ $msg->message }}</p>
                                            @endif

                                            <div class="flex items-center justify-end gap-1 mt-1 opacity-75">
                                                <span class="text-[10px] text-gray-500">{{ $msg->created_at->format('d M Y, H:i') }}</span>
                                                @if($msg->direction == 'out')
                                                    <svg class="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Tombol Balas Pesan Responden (Sisi Kanan Bubble Masuk) --}}
                                        @if($canReply && $msg->direction == 'in')
                                            <button type="button"
                                                    onclick="selectReply(this)"
                                                    data-msg-id="{{ $msg->id }}"
                                                    data-msg-sender="{{ $replySenderLabel }}"
                                                    data-msg-preview="{{ $replyPreviewSingleLine }}"
                                                    class="opacity-60 sm:opacity-0 group-hover:opacity-100 focus:opacity-100 mb-2 w-7 h-7 rounded-full bg-white hover:bg-indigo-50 text-gray-500 hover:text-indigo-600 shadow-sm border border-gray-200 flex items-center justify-center transition shrink-0"
                                                    title="Balas pesan ini">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            {{-- Footer Input Balasan Agen --}}
                            @if($activeTicket->status === 'open')

                                @if(is_null($activeTicket->agent_id))
                                    <div class="p-6 sm:p-8 bg-gray-50 border-t border-gray-200 flex flex-col items-center justify-center text-center shrink-0">
                                        <div class="bg-indigo-100 text-indigo-600 p-3 rounded-full mb-3 shadow-sm">
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

                                @elseif($activeTicket->agent_id !== auth()->id() && !auth()->user()->isSuperadmin() && !auth()->user()->isAdmin())
                                    <div class="p-4 bg-amber-50 border-t border-amber-200 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                                        <span class="text-amber-700 text-sm font-semibold flex items-center gap-2">
                                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            Tiket ini sedang dilayani oleh <strong>{{ $activeTicket->agent ? $activeTicket->agent->name : 'agen lain' }}</strong>.
                                        </span>
                                        <form action="{{ route('messages.transfer', $activeTicket->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengambil alih tiket ini?');">
                                            @csrf
                                            <input type="hidden" name="action" value="take_over">
                                            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold px-3.5 py-2 rounded-lg transition shadow-sm flex items-center gap-1.5 shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                                Ambil Alih Tiket
                                            </button>
                                        </form>
                                    </div>

                                @else
                                    <div class="p-3 bg-white border-t border-gray-200/80 shrink-0">
                                        <form id="chat-reply-form" action="{{ route('messages.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2" onsubmit="return handleChatSubmit(this)">
                                            @csrf
                                            <input type="hidden" name="device_id" value="{{ $activeTicket->device_id }}">
                                            <input type="hidden" name="number" value="{{ $activeTicket->number }}">
                                            <input type="hidden" name="reply_to_id" id="reply-to-id" value="">

                                            {{-- Preview Pesan yang Sedang Dibalas (Quote Reply) --}}
                                            <div id="reply-preview-container" class="hidden px-3.5 py-2 bg-gray-50 border border-gray-200 border-l-4 border-l-indigo-500 rounded-xl text-xs flex justify-between items-center gap-2">
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-1.5 font-bold text-indigo-700">
                                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                                        <span id="reply-sender-display" class="truncate">Membalas pesan</span>
                                                    </div>
                                                    <p id="reply-text-display" class="text-gray-600 truncate mt-0.5"></p>
                                                </div>
                                                <button type="button" onclick="cancelReply()" class="text-gray-400 hover:text-rose-600 p-1 rounded-lg hover:bg-gray-200/60 transition shrink-0" title="Batal balas">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </div>

                                            <div id="file-preview-container" class="hidden px-3.5 py-2 bg-indigo-50 border border-indigo-100 rounded-xl text-xs flex justify-between items-center text-indigo-700 font-medium">
                                                <div class="flex items-center gap-2 overflow-hidden">
                                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                    <span id="file-name-display" class="truncate font-semibold">nama-file.jpg</span>
                                                </div>
                                                <button type="button" onclick="removeFile()" class="text-rose-600 hover:text-rose-800 font-bold ml-2 text-xs">✕ Hapus</button>
                                            </div>

                                            <div class="flex gap-2 items-end">
                                                <label class="cursor-pointer bg-gray-50 border border-gray-300 hover:bg-gray-100 text-gray-500 hover:text-indigo-600 rounded-xl p-2.5 shadow-sm transition flex items-center justify-center shrink-0 w-10 h-10" title="Lampirkan Dokumen/Foto">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                    <input type="file" name="file" id="file-upload" class="hidden" onchange="showFilePreview(this)">
                                                </label>

                                                <textarea id="chat-message-input" name="message" rows="1"
                                                          oninput="this.style.height = 'auto'; this.style.height = Math.min(this.scrollHeight, 112) + 'px';"
                                                          onkeydown="if(event.key === 'Escape') { cancelReply(); } else if(event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); if(this.value.trim() || document.getElementById('file-upload').value) { this.form.requestSubmit(); } }"
                                                          class="flex-1 resize-none border-gray-300 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-4 text-sm max-h-28 overflow-y-auto"
                                                          placeholder="Ketik pesan balasan... (Tekan Enter untuk kirim)"></textarea>

                                                <button id="chat-send-btn" type="submit" class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white rounded-xl p-2.5 shadow-sm transition flex items-center justify-center shrink-0 w-10 h-10" title="Kirim Pesan">
                                                    <svg class="w-5 h-5 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif

                            @else
                                <div class="p-3 bg-emerald-50/80 border-t border-emerald-200 text-center shrink-0 flex items-center justify-center gap-2">
                                    <span class="inline-flex items-center gap-1.5 text-emerald-800 text-xs font-semibold bg-emerald-100/90 border border-emerald-300 px-3.5 py-1 rounded-full shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Sesi obrolan tiket ini telah selesai.
                                    </span>
                                </div>
                            @endif

                        @else
                            {{-- Tampilan Kosong Saat Belum Memilih Tiket --}}
                            <div class="flex-1 flex flex-col items-center justify-center text-center px-4">
                                <div class="w-20 h-20 rounded-2xl bg-white shadow-xs flex items-center justify-center text-indigo-500 mb-4 border border-gray-200">
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
        <div class="absolute top-4 right-4 z-20 flex items-center gap-2">
            <a id="lightboxDownloadBtn" href="#" class="px-3.5 py-2 bg-white/20 hover:bg-white/30 text-white text-xs font-semibold rounded-xl backdrop-blur-md transition flex items-center gap-1.5 shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                <span>Unduh Gambar</span>
            </a>
            <button onclick="closeImageModal()" class="text-white hover:text-gray-300 p-2 bg-black bg-opacity-50 hover:bg-opacity-70 rounded-full transition" title="Tutup">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
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
        // --- BALAS PESAN TERTENTU (QUOTE REPLY) ---
        function selectReply(btn) {
            let msgId = btn.dataset.msgId;
            let sender = btn.dataset.msgSender || 'Pesan';
            let preview = btn.dataset.msgPreview || '[Lampiran Media]';

            let replyInput = document.getElementById('reply-to-id');
            let previewBox = document.getElementById('reply-preview-container');
            let senderEl = document.getElementById('reply-sender-display');
            let textEl = document.getElementById('reply-text-display');

            if (!replyInput || !previewBox) return;

            replyInput.value = msgId;
            senderEl.textContent = 'Membalas ' + sender;
            textEl.textContent = preview;
            previewBox.classList.remove('hidden');

            let chatInput = document.getElementById('chat-message-input');
            if (chatInput) {
                chatInput.focus();
            }
        }

        function cancelReply() {
            let replyInput = document.getElementById('reply-to-id');
            let previewBox = document.getElementById('reply-preview-container');
            if (replyInput) replyInput.value = '';
            if (previewBox) previewBox.classList.add('hidden');
        }

        function scrollToMessage(msgId) {
            let row = document.getElementById('msg-' + msgId);
            if (!row) return;

            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            let bubble = row.querySelector('.message-bubble');
            if (bubble) {
                bubble.classList.add('ring-2', 'ring-indigo-500', 'ring-offset-2');
                setTimeout(() => {
                    bubble.classList.remove('ring-2', 'ring-indigo-500', 'ring-offset-2');
                }, 1600);
            }
        }

        function handleChatSubmit(form) {
            if (form.dataset.submitting === 'true') {
                return false;
            }
            let input = document.getElementById('chat-message-input');
            let fileInput = document.getElementById('file-upload');
            let hasText = input && input.value.trim().length > 0;
            let hasFile = fileInput && fileInput.files && fileInput.files.length > 0;

            if (!hasText && !hasFile) {
                return false;
            }

            form.dataset.submitting = 'true';
            let btn = document.getElementById('chat-send-btn');
            if (btn) {
                btn.disabled = true;
            }
            return true;
        }

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
        function openImageModal(imageUrl, downloadUrl = '') {
            let modal = document.getElementById('imageLightboxModal');
            let img = document.getElementById('lightboxImage');
            let dlBtn = document.getElementById('lightboxDownloadBtn');
            img.src = imageUrl;
            if (dlBtn) {
                dlBtn.href = downloadUrl || imageUrl;
            }
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

                    // Update sidebar tabs & counter badges silently
                    let newTabs = doc.getElementById('sidebar-tabs');
                    let oldTabs = document.getElementById('sidebar-tabs');
                    if (newTabs && oldTabs) {
                        oldTabs.innerHTML = newTabs.innerHTML;
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
