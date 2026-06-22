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
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Live Chat') }}
            </h2>
            <button onclick="document.getElementById('newMessageModal').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md text-sm transition duration-150 shadow-sm">
                + Pesan Baru
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">

                <div class="h-[calc(100vh-240px)] min-h-[500px] flex overflow-hidden bg-white">

                    <div class="w-1/3 bg-gray-50 border-r border-gray-200 flex flex-col">

                        <div class="p-4 border-b border-gray-200 bg-white shadow-sm z-10 flex flex-col gap-3">
                            <div>
                                <h2 class="text-lg font-bold text-gray-800">Antrean Pesan</h2>
                                <p class="text-xs text-gray-500">Pilih pesan untuk melayani pelanggan</p>
                            </div>

                            <form action="{{ route('messages.index') }}" method="GET" class="relative">
                                @if(request('ticket_id'))
                                    <input type="hidden" name="ticket_id" value="{{ request('ticket_id') }}">
                                @endif

                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, nomor, atau tiket..." class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 pl-9 py-2 transition-all">
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

                        <div class="flex-1 overflow-y-auto" id="sidebar-list">
                            @forelse($tickets as $t)
                                @php
                                    $contact = $contacts->firstWhere('number', $t->number);
                                    $displayName = $contact ? $contact->name : ($t->customer_name ?: 'Pelanggan Baru');

                                    $currentTag = strtolower($t->tag ?? 'umum');
                                    $tagClass = $tagColors[$currentTag] ?? $tagColors['umum'];
                                @endphp

                                <a href="{{ route('messages.index', ['ticket_id' => $t->id, 'search' => request('search')]) }}" class="block p-4 border-b border-gray-100 hover:bg-gray-100 transition {{ request('ticket_id') == $t->id ? 'bg-indigo-50 border-l-4 border-indigo-500' : '' }} {{ $t->status === 'closed' ? 'opacity-75 bg-gray-50/50' : '' }}">
                                    <div class="flex justify-between items-start mb-1">

                                        <div class="flex flex-col truncate pr-2">
                                            <span class="font-bold text-sm truncate {{ $t->has_new_message ? 'text-indigo-900 font-extrabold' : 'text-gray-800' }} flex items-center gap-1.5">
                                                {{ $displayName }}
                                                @if($contact)
                                                    <span class="bg-emerald-100 text-emerald-700 text-[8px] uppercase px-1 py-0.5 rounded-sm font-bold leading-none mt-0.5" title="Tersimpan di Kontak">Kontak</span>
                                                @endif
                                            </span>
                                            <span class="text-xs text-gray-500 font-mono mt-0.5">{{ $t->number }}</span>
                                        </div>

                                        <div class="flex gap-1 items-center flex-shrink-0">
                                            @if($t->has_new_message)
                                                <span class="bg-blue-600 text-white text-[9px] font-extrabold px-2 py-0.5 rounded-full animate-pulse shadow-sm">PESAN BARU</span>
                                            @endif

                                            @if($t->status === 'closed')
                                                <span class="bg-gray-200 text-gray-600 text-[10px] font-bold px-2 py-0.5 rounded-full">Selesai</span>
                                            @elseif(is_null($t->agent_id))
                                                <span class="bg-red-100 text-red-600 text-[10px] font-bold px-2 py-0.5 rounded-full">Baru</span>
                                            @else
                                                <span class="bg-yellow-100 text-yellow-700 text-[10px] font-bold px-2 py-0.5 rounded-full">Diproses</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-[11px] text-gray-400 mt-1 flex flex-wrap gap-1 items-center">
                                        <span>Tiket: <span class="font-mono text-indigo-500">{{ $t->ticket_number }}</span></span>
                                        <span class="{{ $tagClass }} border px-1.5 py-0.5 rounded text-[9px] uppercase font-bold">{{ $t->tag ?? 'Umum' }}</span>
                                        <br>
                                        @if($t->agent) <span class="w-full block text-gray-400">Dilayani: {{ $t->agent->name }}</span> @endif
                                    </div>
                                </a>
                            @empty
                                <div class="p-8 flex flex-col items-center justify-center text-center text-gray-400">
                                    <span class="text-sm">Tidak ada riwayat tiket aktif.</span>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="w-2/3 flex flex-col bg-[#efeae2]">
                        @if($activeTicket)
                            <div class="bg-white p-4 border-b border-gray-200 flex justify-between items-center shadow-sm z-10">
                                <div>
                                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                        {{ $contactName }}

                                        @php
                                            $isContact = $contacts->contains('number', $activeTicket->number);
                                        @endphp

                                        @if($isContact)
                                            <span class="bg-emerald-100 text-emerald-700 border border-emerald-300 text-[10px] uppercase font-bold px-2 py-0.5 rounded flex items-center gap-1 shadow-sm">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Kontak
                                            </span>
                                        @else
                                            <button onclick="openAddContactModal('{{ $activeTicket->number }}', '{{ $activeTicket->customer_name }}')" class="bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 hover:text-indigo-600 text-[10px] uppercase font-bold px-2 py-0.5 rounded shadow-sm transition flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                Tambah Kontak
                                            </button>
                                        @endif

                                        @php
                                            $activeTag = strtolower($activeTicket->tag ?? 'umum');
                                            $activeTagClass = $tagColors[$activeTag] ?? $tagColors['umum'];
                                        @endphp
                                        <span class="{{ $activeTagClass }} border text-[10px] uppercase font-bold px-2 py-0.5 rounded shadow-sm ml-2">
                                            {{ $activeTicket->tag ?? 'Umum' }}
                                        </span>
                                    </h2>
                                    <p class="text-xs text-gray-500">{{ $activeTicket->number }} • Tiket: {{ $activeTicket->ticket_number }} ({{ strtoupper($activeTicket->status) }})</p>
                                </div>

                                <div class="flex gap-2 items-center">
                                    @if($activeTicket->status === 'open')
                                        @if($activeTicket->agent_id === auth()->id() || auth()->user()->isSuperadmin())
                                            <form action="{{ route('messages.update_tag', $activeTicket->id) }}" method="POST" class="flex items-center">
                                                @csrf
                                                <select name="tag" onchange="this.form.submit()" class="text-xs border-gray-300 rounded-l-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 pr-8 pl-3 bg-gray-50 hover:bg-white transition cursor-pointer">
                                                    <option value="umum" {{ $activeTicket->tag == 'umum' ? 'selected' : '' }}>Umum</option>
                                                    <option value="pengaduan" {{ $activeTicket->tag == 'pengaduan' ? 'selected' : '' }}>Pengaduan</option>
                                                    <option value="permintaan data" {{ $activeTicket->tag == 'permintaan data' ? 'selected' : '' }}>Permintaan Data</option>
                                                    <option value="promosi" {{ $activeTicket->tag == 'promosi' ? 'selected' : '' }}>Promosi</option>
                                                    <option value="rekrutmen" {{ $activeTicket->tag == 'rekrutmen' ? 'selected' : '' }}>Rekrutmen</option>
                                                    <option value="konsultasi" {{ $activeTicket->tag == 'konsultasi' ? 'selected' : '' }}>Konsultasi</option>
                                                </select>
                                                <div class="bg-gray-200 text-gray-600 px-3 py-2 text-xs font-semibold rounded-r-md border border-l-0 border-gray-300">Set Tag</div>
                                            </form>

                                            <form action="{{ route('messages.close', $activeTicket->id) }}" method="POST" onsubmit="return confirm('Tutup tiket ini?');">
                                                @csrf
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-md transition shadow-sm flex items-center">
                                                    ✓ Selesai
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <span class="bg-gray-100 text-gray-500 border border-gray-300 text-xs font-semibold px-4 py-2 rounded-full shadow-inner">
                                            🔒 Tiket Selesai
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex-1 overflow-y-auto p-6 space-y-4 flex flex-col" id="chat-messages">
                                @foreach($messages as $msg)
                                    <div class="flex w-full {{ $msg->direction == 'out' ? 'justify-end' : 'justify-start' }}">
                                        <div class="max-w-[75%] rounded-lg p-3 shadow-sm relative {{ $msg->direction == 'out' ? 'bg-[#dcf8c6] text-gray-800 rounded-tr-none' : 'bg-white text-gray-800 rounded-tl-none' }}">

                                            @if($msg->quoted_message)
                                                <div class="mb-2 p-2 rounded bg-black bg-opacity-5 border-l-4 {{ $msg->direction == 'out' ? 'border-emerald-600' : 'border-indigo-500' }} text-xs text-gray-600">
                                                    <span class="block font-bold {{ $msg->direction == 'out' ? 'text-emerald-700' : 'text-indigo-600' }} mb-0.5">Membalas pesan:</span>
                                                    <p class="whitespace-pre-wrap line-clamp-3 opacity-80">{{ $msg->quoted_message }}</p>
                                                </div>
                                            @endif

                                            @if($msg->media_url)
                                                @php
                                                    // Tambahkan pengecekan untuk 'sent_media/' menggunakan operator OR (||)
                                                    $isLocal = str_starts_with($msg->media_url, 'received_media/') || str_starts_with($msg->media_url, 'sent_media/');

                                                    if ($isLocal) {
                                                        $fullMediaUrl = asset('storage/' . $msg->media_url);
                                                        $extension = strtolower(pathinfo($msg->media_url, PATHINFO_EXTENSION));
                                                    } else {
                                                        // Cek apakah ini URL eksternal utuh (misal kiriman dari API Gateway)
                                                        if (str_starts_with($msg->media_url, 'http://') || str_starts_with($msg->media_url, 'https://')) {
                                                            $fullMediaUrl = $msg->media_url;
                                                            $cleanPath = parse_url($msg->media_url, PHP_URL_PATH);
                                                        } else {
                                                            // Jika bukan, asumsikan ini path relatif dari server GOWA
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
                                                        <a href="javascript:void(0)" onclick="openImageModal('{{ $fullMediaUrl }}')" title="Klik untuk memperbesar">
                                                            <img src="{{ $fullMediaUrl }}" class="rounded-lg max-h-64 object-cover cursor-pointer hover:opacity-90 transition border border-gray-200" alt="Gambar Lampiran" onerror="this.onerror=null; this.src='https://placehold.co/300x200?text=Gambar+Gagal+Dimuat';">
                                                        </a>
                                                    @else
                                                        <a href="{{ $fullMediaUrl }}" target="_blank" class="flex items-center gap-3 p-3 bg-black bg-opacity-5 hover:bg-opacity-10 transition rounded-lg border border-gray-200 max-w-xs">
                                                            <div class="bg-indigo-500 text-white p-2 rounded flex-shrink-0">
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

                                            @if($msg->message)
                                                <p class="text-sm whitespace-pre-wrap">{{ $msg->message }}</p>
                                            @endif

                                            <span class="text-[10px] text-gray-500 block text-right mt-1 opacity-80">{{ $msg->created_at->format('d M, H:i') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if($activeTicket->status === 'open')

                                @if(is_null($activeTicket->agent_id))
                                    <div class="p-8 bg-gray-50 border-t border-gray-200 flex flex-col items-center justify-center text-center">
                                        <div class="bg-indigo-100 text-indigo-600 p-3 rounded-full mb-3 shadow-sm">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                                        </div>
                                        <h3 class="text-lg font-bold text-gray-800 mb-2">Tiket Dalam Antrean</h3>
                                        <p class="text-sm text-gray-500 mb-5 max-w-md">Anda sedang dalam mode pratinjau (Preview). Jika Anda memiliki kapasitas dan siap melayani permintaan ini, silakan ambil tiketnya.</p>

                                        <form action="{{ route('messages.start', $activeTicket->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-lg shadow transition flex items-center gap-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
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

                                            <div id="file-preview-container" class="hidden px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-lg text-xs flex justify-between items-center text-indigo-700 font-medium">
                                                <div class="flex items-center gap-2 overflow-hidden">
                                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                    <span id="file-name-display" class="truncate">nama-file.jpg</span>
                                                </div>
                                                <button type="button" onclick="removeFile()" class="text-red-500 hover:text-red-700 font-bold ml-2">✕ Hapus</button>
                                            </div>

                                            <div class="flex gap-2 items-end">
                                                <label class="cursor-pointer bg-white border border-gray-300 hover:bg-gray-100 text-gray-500 rounded-full p-3 shadow-sm transition flex items-center justify-center flex-shrink-0 w-12 h-12" title="Lampirkan Dokumen/Gambar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                                                    <input type="file" name="file" id="file-upload" class="hidden" onchange="showFilePreview(this)">
                                                </label>

                                                <textarea name="message" rows="1" class="flex-1 resize-none border-gray-300 rounded-full shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3 px-5 text-sm" placeholder="Ketik pesan atau caption gambar..."></textarea>

                                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-3 shadow transition flex items-center justify-center flex-shrink-0 w-12 h-12">
                                                    <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif

                            @else
                                <div class="p-4 bg-gray-50 border-t border-gray-200 text-center">
                                    <span class="text-gray-500 text-sm font-medium">Sesi obrolan ini telah ditutup.</span>
                                </div>
                            @endif

                        @else
                            <div class="flex-1 flex flex-col items-center justify-center text-center px-4">
                                <h2 class="text-2xl font-light text-gray-700">SIMAWA Live Chat</h2>
                                <p class="text-gray-500 mt-3 max-w-md text-sm">Pilih antrean pesan di sebelah kiri, atau klik tombol <b>+ Pesan Baru</b> di kanan atas untuk memulai percakapan baru.</p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div id="newMessageModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="document.getElementById('newMessageModal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-visible shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full">
                <form action="{{ route('messages.store') }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 rounded-t-lg">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4 border-b pb-2">Kirim Pesan Baru</h3>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Pilih Device Pengirim</label>
                            <select name="device_id" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500" required>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">
                                        {{ $device->name }} ({{ $device->phone_number ?? 'Nomor belum diset' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <div class="flex justify-between items-end mb-1">
                                <label class="block text-sm font-medium text-gray-700">Nomor Tujuan</label>
                                <select onchange="if(this.value) { let target = document.getElementById('target_numbers'); target.value = target.value ? target.value + ', ' + this.value : this.value; this.value = ''; }" class="text-xs border-gray-300 rounded shadow-sm focus:border-indigo-500 py-1 pl-2 pr-6">
                                    <option value="">+ Sisipkan dari Buku Telepon</option>
                                    @foreach($contacts as $contact)
                                        <option value="{{ $contact->number }}">{{ $contact->name }} ({{ $contact->number }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <textarea id="target_numbers" name="number" rows="2" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 font-mono text-indigo-600 text-sm" placeholder="Contoh: 081234567, 628987654" required></textarea>
                        </div>

                        <div class="mb-2">
                            <label class="block text-sm font-medium text-gray-700">Isi Pesan</label>
                            <textarea name="message" rows="5" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 text-sm" placeholder="Ketik pesan Anda di sini..." required></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-lg">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm" onclick="this.innerHTML='Mengirim...';">
                            Kirim Pesan
                        </button>
                        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="document.getElementById('newMessageModal').classList.add('hidden')">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="imageLightboxModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black bg-opacity-80 backdrop-blur-sm transition-opacity cursor-pointer" onclick="closeImageModal()"></div>
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10 p-2 bg-black bg-opacity-50 rounded-full">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <div class="relative z-10 max-w-4xl max-h-screen">
            <img id="lightboxImage" src="" class="max-w-full max-h-[90vh] object-contain rounded shadow-2xl" alt="Preview Gambar">
        </div>
    </div>

    <div id="addContactModal" class="fixed inset-0 z-[70] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeAddContactModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-visible shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <form action="{{route('phonebook.store')}}" method="POST" id="formAddContact">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 rounded-t-lg">
                        <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4 border-b pb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                            Simpan ke Buku Telepon
                        </h3>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
                            <input type="text" name="number" id="contactModalNumber" class="mt-1 w-full border-gray-300 rounded-md shadow-sm bg-gray-100 focus:ring-0 focus:border-gray-300 text-sm font-mono text-gray-600" readonly required>
                            <p class="text-[10px] text-gray-500 mt-1">Nomor sudah terisi otomatis dari data tiket.</p>
                        </div>

                        <div class="mb-2">
                            <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                            <input type="text" name="name" id="contactModalName" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Contoh: Budi Santoso" required autofocus>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-lg">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan Kontak
                        </button>
                        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeAddContactModal()">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        // --- FUNGSI PREVIEW UPLOAD FILE ---
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
        // --- FUNGSI MODAL TAMBAH KONTAK ---
        function openAddContactModal(number, name) {
            document.getElementById('addContactModal').classList.remove('hidden');
            document.getElementById('contactModalNumber').value = number;

            // Isi otomatis saran nama dari profil WA (jika ada)
            let nameInput = document.getElementById('contactModalName');
            nameInput.value = name ? name : '';
            nameInput.focus();
        }

        function closeAddContactModal() {
            document.getElementById('addContactModal').classList.add('hidden');
        }
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

                    // UPDATE SIDEBAR TIKET SILENTLY
                    let newSidebar = doc.getElementById('sidebar-list');
                    let oldSidebar = document.getElementById('sidebar-list');
                    if (newSidebar && oldSidebar) {
                        oldSidebar.innerHTML = newSidebar.innerHTML;
                    }

                    // UPDATE RUANG CHAT SILENTLY
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
