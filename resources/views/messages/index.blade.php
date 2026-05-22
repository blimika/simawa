<x-app-layout>
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

                        // PRIORITAS:
                        // 1. Jika ada di phonebook -> Tampilkan nama phonebook
                        // 2. Jika tidak ada -> Tampilkan nama dari WA (customer_name)
                        // 3. Jika null/kosong -> Tampilkan 'Pelanggan Baru'
                        $displayName = $contact ? $contact->name : ($t->customer_name ?: 'Pelanggan Baru');
                    @endphp

                    <a href="{{ route('messages.index', ['ticket_id' => $t->id, 'search' => request('search')]) }}" class="block p-4 border-b border-gray-100 hover:bg-gray-100 transition {{ request('ticket_id') == $t->id ? 'bg-indigo-50 border-l-4 border-indigo-500' : '' }} {{ $t->status === 'closed' ? 'opacity-75 bg-gray-50/50' : '' }}">
                        <div class="flex justify-between items-start mb-1">

                            <div class="flex flex-col truncate pr-2">
                                <span class="font-bold text-sm truncate {{ $t->has_new_message ? 'text-indigo-900 font-extrabold' : 'text-gray-800' }}">
                                    {{ $displayName }}
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
                        <div class="text-[11px] text-gray-400 mt-1">
                            Tiket: <span class="font-mono text-indigo-500">{{ $t->ticket_number }}</span> <br>
                            @if($t->agent) Dilayani: {{ $t->agent->name }} @endif
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
                        <h2 class="text-lg font-bold text-gray-800">{{ $contactName }}</h2>
                        <p class="text-xs text-gray-500">{{ $activeTicket->number }} • Tiket: {{ $activeTicket->ticket_number }} ({{ strtoupper($activeTicket->status) }})</p>
                    </div>

                    @if($activeTicket->status === 'open')
                        <form action="{{ route('messages.close', $activeTicket->id) }}" method="POST" onsubmit="return confirm('Tutup tiket ini?');">
                            @csrf
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-full transition shadow-sm flex items-center">
                                ✓ Tandai Selesai
                            </button>
                        </form>
                    @else
                        <span class="bg-gray-100 text-gray-500 border border-gray-300 text-xs font-semibold px-4 py-2 rounded-full shadow-inner">
                            🔒 Tiket Selesai
                        </span>
                    @endif
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-4 flex flex-col" id="chat-messages">
                    @foreach($messages as $msg)
                        <div class="flex w-full {{ $msg->direction == 'out' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[75%] rounded-lg p-3 shadow-sm relative {{ $msg->direction == 'out' ? 'bg-[#dcf8c6] text-gray-800 rounded-tr-none' : 'bg-white text-gray-800 rounded-tl-none' }}">

                                @if($msg->media_url)
                                    @php
                                        // CEK: Apakah ini file lokal SIMAWA atau file lama GOWA?
                                        $isLocal = str_starts_with($msg->media_url, 'received_media/');

                                        if ($isLocal) {
                                            // Gunakan URL lokal dari folder storage kita sendiri
                                            $fullMediaUrl = asset('storage/' . $msg->media_url);
                                            $extension = strtolower(pathinfo($msg->media_url, PATHINFO_EXTENSION));
                                        } else {
                                            // Fallback untuk riwayat chat lama (sebelum fitur download dipasang)
                                            $gowaBaseUrl = env('GOWA_API_URL', 'http://localhost:5271');
                                            $cleanPath = str_replace('\\', '/', $msg->media_url);
                                            $fullMediaUrl = rtrim($gowaBaseUrl, '/') . '/' . ltrim($cleanPath, '/');
                                            $extension = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));
                                        }

                                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'];
                                        $isImage = in_array($extension, $imageExtensions);
                                    @endphp

                                    <div class="mb-2">
                                        @if($isImage)
                                            <a href="javascript:void(0)" onclick="openImageModal('{{ $fullMediaUrl }}')" title="Klik untuk memperbesar">
                                                <img src="{{ $fullMediaUrl }}"
                                                     class="rounded-lg max-h-64 object-cover cursor-pointer hover:opacity-90 transition border border-gray-200"
                                                     alt="Gambar Lampiran"
                                                     onerror="this.onerror=null; this.src='https://placehold.co/300x200?text=Gambar+Gagal+Dimuat';">
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

                                @if($msg->quoted_message)
                                    <div class="mb-2 p-2 rounded bg-black bg-opacity-5 border-l-4 {{ $msg->direction == 'out' ? 'border-emerald-600' : 'border-indigo-500' }} text-xs text-gray-600">
                                        <span class="block font-bold {{ $msg->direction == 'out' ? 'text-emerald-700' : 'text-indigo-600' }} mb-0.5">Membalas pesan:</span>
                                        <p class="whitespace-pre-wrap line-clamp-3 opacity-80">{{ $msg->quoted_message }}</p>
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

                <div class="bg-gray-100 p-4 border-t border-gray-300">
                    <form action="{{ route('messages.store') }}" method="POST" class="flex gap-3 items-end">
                        @csrf
                        <input type="hidden" name="device_id" value="{{ $activeTicket->device_id }}">
                        <input type="hidden" name="number" value="{{ $activeTicket->number }}">
                        <textarea name="message" rows="2" class="flex-1 rounded-lg border-gray-300 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm px-4 py-3 text-sm resize-none" placeholder="{{ $activeTicket->status === 'closed' ? 'Ketik untuk membuka kembali tiket dan membalas...' : 'Ketik balasan untuk pelanggan...' }}" required autofocus></textarea>
                        <button type="submit" class="bg-slate-700 hover:bg-slate-800 text-white rounded-full h-11 w-11 flex-shrink-0 flex items-center justify-center transition shadow mb-1">➤</button>
                    </form>
                </div>

            @else
                <div class="flex-1 flex flex-col items-center justify-center text-center px-4">
                    <h2 class="text-2xl font-light text-gray-700">SIMAWA Live Chat</h2>
                    <p class="text-gray-500 mt-3 max-w-md text-sm">Pilih antrean pesan di sebelah kiri, atau klik tombol <b>+ Pesan Baru</b> di kanan atas untuk memulai percakapan baru.</p>
                </div>
            @endif
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
                            <label class="block text-sm font-medium text-gray-700">Pilih Device</label>
                            <select name="device_id" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500" required>
                                @foreach($devices as $dev)
                                    <option value="{{ $dev->id }}">{{ $dev->name }} ({{ $dev->number }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Nomor Tujuan</label>
                            <textarea name="number" rows="2" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 font-mono text-sm" placeholder="Contoh: 081234567" required></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="block text-sm font-medium text-gray-700">Isi Pesan</label>
                            <textarea name="message" rows="5" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 text-sm" placeholder="Ketik pesan..." required></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-lg">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto">Kirim Pesan</button>
                        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md bg-white text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto" onclick="document.getElementById('newMessageModal').classList.add('hidden')">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="imageLightboxModal" class="fixed inset-0 z-[60] hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black bg-opacity-80 backdrop-blur-sm transition-opacity cursor-pointer" onclick="closeImageModal()"></div>

        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10 focus:outline-none p-2 bg-black bg-opacity-50 rounded-full">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>

        <div class="relative z-10 max-w-4xl max-h-screen">
            <img id="lightboxImage" src="" class="max-w-full max-h-[90vh] object-contain rounded shadow-2xl" alt="Preview Gambar Membesar">
        </div>
    </div>

    </div> </div> </div> </div>
    <script>
        // --- FUNGSI UNTUK MEMBUKA & MENUTUP LIGHTBOX GAMBAR ---
        function openImageModal(imageUrl) {
            let modal = document.getElementById('imageLightboxModal');
            let img = document.getElementById('lightboxImage');

            img.src = imageUrl; // Isi source gambar dengan URL yang diklik
            modal.classList.remove('hidden'); // Tampilkan pop-up
        }

        function closeImageModal() {
            let modal = document.getElementById('imageLightboxModal');
            modal.classList.add('hidden'); // Sembunyikan pop-up

            // Hapus gambar setelah jeda sedikit agar tidak "berkedip" saat tertutup
            setTimeout(() => {
                document.getElementById('lightboxImage').src = '';
            }, 300);
        }

        // --- FUNGSI AUTO REFRESH SILENT POLLING ---
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

                    let newSidebar = doc.getElementById('sidebar-list');
                    let oldSidebar = document.getElementById('sidebar-list');
                    if (newSidebar && oldSidebar) {
                        oldSidebar.innerHTML = newSidebar.innerHTML;
                    }

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
