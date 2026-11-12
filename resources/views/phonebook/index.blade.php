<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Buku Telepon (Phonebook)') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Kelola kontak lokal SIMAWA dan sinkronkan dengan kontak dari akun WhatsApp (GOWA).
                </p>
            </div>
            <a href="{{ route('phonebook.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Kontak Manual
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen" x-data="{
        singleModalOpen: false,
        importAllModalOpen: false,
        modalName: '',
        modalNumber: '',
        modalCategory: '',
        openSaveModal(name, number) {
            this.modalName = name;
            this.modalNumber = number;
            this.modalCategory = '';
            this.singleModalOpen = true;
        }
    }">
        <div class="w-full px-4 sm:px-6 lg:px-8">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="mb-5 bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3.5 rounded-xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 bg-red-50 border border-red-300 text-red-800 px-4 py-3.5 rounded-xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">&times;</button>
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div class="mb-5 bg-red-50 border border-red-300 text-red-800 px-4 py-3.5 rounded-xl shadow-sm">
                    <ul class="list-disc list-inside text-sm space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Navigasi Tab Utama --}}
            <div class="mb-6 border-b border-gray-200">
                <nav class="flex space-x-4 sm:space-x-8" aria-label="Tabs">
                    {{-- Tab 1: Buku Telepon SIMAWA --}}
                    <a href="{{ route('phonebook.index', ['tab' => 'phonebook']) }}"
                       class="inline-flex items-center gap-2 py-4 px-1 border-b-2 font-semibold text-sm transition {{ $tab !== 'whatsapp' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span>Buku Telepon SIMAWA</span>
                        <span class="ml-1.5 py-0.5 px-2.5 rounded-full text-xs font-bold {{ $tab !== 'whatsapp' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $contacts->total() }}
                        </span>
                    </a>

                    {{-- Tab 2: Kontak WhatsApp (GOWA) --}}
                    <a href="{{ route('phonebook.index', ['tab' => 'whatsapp']) }}"
                       class="inline-flex items-center gap-2 py-4 px-1 border-b-2 font-semibold text-sm transition {{ $tab === 'whatsapp' ? 'border-emerald-600 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.65 3.742-.983zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        <span>Kontak WhatsApp (GOWA)</span>
                        @if($tab === 'whatsapp')
                            <span class="ml-1.5 py-0.5 px-2.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                {{ number_format($totalWaContacts) }}
                            </span>
                        @else
                            <span class="ml-1.5 py-0.5 px-2 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">API</span>
                        @endif
                    </a>
                </nav>
            </div>

            {{-- ======================================================== --}}
            {{-- TAB 1: BUKU TELEPON SIMAWA                               --}}
            {{-- ======================================================== --}}
            @if ($tab !== 'whatsapp')
                {{-- Card Import XLS --}}
                <div class="mb-6 bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-gray-800 text-lg">Import Kontak Massal (.XLS)</h3>
                            <span class="px-2 py-0.5 text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full">Format .xls saja</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1 mb-2.5">
                            Upload file Excel khusus format <strong>.XLS</strong>. Kolom: <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Nama Kontak</code>, <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Nomor WhatsApp</code>, <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs">Kategori</code>.
                        </p>
                        <a href="{{ route('phonebook.template') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 px-3 py-1.5 rounded-lg transition w-max">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Download Template XLS
                        </a>
                    </div>

                    <form action="{{ route('phonebook.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-3 w-full lg:w-auto">
                        @csrf
                        <input type="file" name="file" accept=".xls" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition">
                        <button type="submit" class="w-full sm:w-auto shrink-0 whitespace-nowrap bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-full text-sm font-bold transition shadow-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Import File XLS
                        </button>
                    </form>
                </div>

                {{-- Tabel Buku Telepon SIMAWA --}}
                <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                    <form id="bulkDeleteForm" action="{{ route('phonebook.bulk-delete') }}" method="POST">
                        @csrf

                        <div class="p-4 border-b border-gray-100 bg-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div class="flex items-center gap-4">
                                <h3 class="font-bold text-gray-800 text-base">Daftar Kontak Tersimpan</h3>
                                <div id="bulkDeleteBtnContainer" class="hidden">
                                    <button type="submit" onclick="return confirm('Peringatan: Anda akan menghapus semua kontak terpilih secara permanen. Lanjutkan?')" class="bg-red-500 hover:bg-red-600 text-white px-4 py-1.5 rounded-lg text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Hapus (<span id="selectedCount">0</span>) Terpilih
                                    </button>
                                </div>
                            </div>

                            {{-- Form Pencarian Kontak SIMAWA --}}
                            <div class="w-full md:w-auto">
                                <div class="flex items-center gap-2">
                                    <div class="relative w-full md:w-72">
                                        <input type="text" name="search" value="{{ request('search') }}" form="searchFormSimawa" placeholder="Cari nama, nomor, kategori..." class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 pl-9 py-2">
                                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        @if(request('search'))
                                            <a href="{{ route('phonebook.index', ['tab' => 'phonebook']) }}" class="absolute right-3 top-2.5 text-gray-400 hover:text-red-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </a>
                                        @endif
                                    </div>
                                    <button type="submit" form="searchFormSimawa" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3.5 py-2 rounded-lg text-sm font-semibold transition">
                                        Cari
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 w-10 text-center">
                                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 cursor-pointer">
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Kontak</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nomor WhatsApp</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                                        @if(auth()->user()->isSuperadmin())
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pemilik</th>
                                        @endif
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @forelse ($contacts as $contact)
                                        <tr class="hover:bg-gray-50/70 transition">
                                            <td class="px-6 py-4 text-center">
                                                <input type="checkbox" name="ids[]" value="{{ $contact->id }}" class="item-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 cursor-pointer">
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs uppercase mr-3 shrink-0">
                                                        {{ strtoupper(substr($contact->name, 0, 2)) }}
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-900">{{ $contact->name }}</span>
                                                </div>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-700">
                                                +{{ $contact->number }}
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($contact->category)
                                                    <span class="px-2.5 py-1 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-full">
                                                        {{ $contact->category }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-400 italic text-xs">-</span>
                                                @endif
                                            </td>

                                            @if(auth()->user()->isSuperadmin())
                                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                                    {{ $contact->owner->name ?? 'Admin #' . $contact->user_id }}
                                                </td>
                                            @endif

                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end items-center gap-3">
                                                    <a href="{{ route('messages.index', ['search' => $contact->number]) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1 rounded transition" title="Buka di Live Chat">
                                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.65 3.742-.983z"/></svg>
                                                        Chat
                                                    </a>

                                                    <a href="{{ route('phonebook.edit', $contact->id) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs">Edit</a>

                                                    <button type="button" data-url="{{ route('phonebook.destroy', $contact->id) }}" data-name="{{ $contact->name }}" onclick="deleteSingleContact(this)" class="text-red-600 hover:text-red-900 font-semibold text-xs">
                                                        Hapus
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ auth()->user()->isSuperadmin() ? 6 : 5 }}" class="px-6 py-12 text-center text-sm text-gray-400 bg-gray-50">
                                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                                <p class="font-medium text-gray-600">Belum ada kontak tersimpan</p>
                                                <p class="text-xs text-gray-400 mt-1">Gunakan tombol "Tambah Kontak Manual", import file Excel (.xls), atau sinkronkan dari tab Kontak WhatsApp.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </form>

                    {{-- Form tersembunyi untuk pencarian --}}
                    <form id="searchFormSimawa" action="{{ route('phonebook.index') }}" method="GET">
                        <input type="hidden" name="tab" value="phonebook">
                    </form>

                    @if(method_exists($contacts, 'links'))
                        <div class="p-4 border-t border-gray-100 bg-white">
                            {{ $contacts->links() }}
                        </div>
                    @endif
                </div>

            {{-- ======================================================== --}}
            {{-- TAB 2: KONTAK WHATSAPP (GOWA API: /user/my/contacts)      --}}
            {{-- ======================================================== --}}
            @else
                <div class="space-y-6">

                    {{-- Toolbar Kontrol WhatsApp Device & Aksi --}}
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                        {{-- Pemilihan Device --}}
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full lg:w-auto">
                            <label class="text-sm font-semibold text-gray-700 shrink-0">Pilih Device WhatsApp:</label>
                            @if($devices->isNotEmpty())
                                <form action="{{ route('phonebook.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                                    <input type="hidden" name="tab" value="whatsapp">
                                    <select name="device_id" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 py-2 pl-3 pr-8 font-medium text-gray-800">
                                        @foreach($devices as $dev)
                                            <option value="{{ $dev->id }}" {{ $selectedDevice && $selectedDevice->id === $dev->id ? 'selected' : '' }}>
                                                {{ $dev->name }} ({{ $dev->phone_number ?? 'No Number' }}) - [{{ ucfirst($dev->status) }}]
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            @else
                                <span class="text-sm text-red-500 font-medium">Belum ada device terdaftar.</span>
                            @endif

                            @if($selectedDevice)
                                <div class="flex items-center gap-2">
                                    @if(strtolower($selectedDevice->status) === 'connected')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Connected
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                            {{ ucfirst($selectedDevice->status) }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Tombol Aksi Toolbar --}}
                        <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto justify-start lg:justify-end">
                            @if($selectedDevice)
                                {{-- Tombol Refresh Kontak WhatsApp --}}
                                <a href="{{ route('phonebook.index', ['tab' => 'whatsapp', 'device_id' => $selectedDevice->id, 'refresh_wa' => 1]) }}"
                                   class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 px-3.5 py-2.5 rounded-lg transition"
                                   title="Ambil data kontak terbaru dari server WhatsApp Gateway">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                    Sinkronisasi / Refresh
                                </a>

                                {{-- Tombol Impor Semua Kontak WA --}}
                                @if($totalWaContacts > 0)
                                    <button type="button" @click="importAllModalOpen = true"
                                            class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 px-4 py-2.5 rounded-lg shadow-sm transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                        Impor Semua Kontak WA ({{ number_format($totalWaContacts) }})
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Pesan Error WA jika ada --}}
                    @if ($waError)
                        <div class="bg-amber-50 border border-amber-300 text-amber-900 px-4 py-3.5 rounded-xl shadow-sm flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                            <div class="text-sm">
                                <p class="font-bold">Informasi Koneksi WhatsApp Gateway</p>
                                <p class="mt-0.5">{{ $waError }}</p>
                                <p class="text-xs text-amber-700 mt-1">Pastikan device sudah di-scan QR dan terhubung ke WhatsApp di menu <strong>Device</strong>.</p>
                            </div>
                        </div>
                    @endif

                    {{-- Tabel Kontak WhatsApp --}}
                    <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">

                        {{-- Header Tabel & Pencarian Kontak WA --}}
                        <div class="p-4 border-b border-gray-100 bg-white flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div>
                                <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                                    <span>Daftar Kontak dari Akun WhatsApp</span>
                                    @if($selectedDevice)
                                        <span class="text-xs font-normal text-gray-500">Device: <strong>{{ $selectedDevice->name }}</strong></span>
                                    @endif
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Menampilkan kontak yang tersimpan di nomor WhatsApp Anda melalui endpoint <code>GET /user/my/contacts</code>.
                                </p>
                            </div>

                            {{-- Form Pencarian Kontak WA --}}
                            <form action="{{ route('phonebook.index') }}" method="GET" class="w-full md:w-auto">
                                <input type="hidden" name="tab" value="whatsapp">
                                @if($selectedDevice)
                                    <input type="hidden" name="device_id" value="{{ $selectedDevice->id }}">
                                @endif
                                <div class="flex items-center gap-2">
                                    <div class="relative w-full md:w-72">
                                        <input type="text" name="search_wa" value="{{ request('search_wa') }}" placeholder="Cari nama atau nomor WA..." class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 pl-9 py-2">
                                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        @if(request('search_wa'))
                                            <a href="{{ route('phonebook.index', ['tab' => 'whatsapp', 'device_id' => $selectedDevice->id ?? null]) }}" class="absolute right-3 top-2.5 text-gray-400 hover:text-red-500">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </a>
                                        @endif
                                    </div>
                                    <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3.5 py-2 rounded-lg text-sm font-semibold transition">
                                        Cari
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Form Bulk Import Kontak WA Terpilih --}}
                        <form id="bulkWaForm" action="{{ route('phonebook.import-wa') }}" method="POST">
                            @csrf

                            {{-- Toolbar Aksi Massal Kontak WA (Muncul saat ada checkbox dicentang) --}}
                            <div id="bulkWaToolbar" class="hidden bg-emerald-50 border-b border-emerald-200 p-3.5 px-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 transition">
                                <div class="flex items-center gap-2 text-emerald-900 font-semibold text-sm">
                                    <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                    <span><span id="waSelectedCount">0</span> kontak WhatsApp dipilih</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                                    <input type="text" name="category" list="waCategoryList" placeholder="Kategori (opsional)..." class="text-xs border-emerald-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 py-1.5 px-3 bg-white">
                                    <datalist id="waCategoryList">
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat }}">
                                        @endforeach
                                    </datalist>

                                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-1.5 rounded-lg text-xs font-bold transition shadow-sm flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        Simpan Terpilih ke Phonebook
                                    </button>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 w-10 text-center">
                                                <input type="checkbox" id="selectAllWa" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500 cursor-pointer">
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Kontak di WhatsApp</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nomor WhatsApp</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status di SIMAWA</th>
                                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @if($waContacts && count($waContacts) > 0)
                                            @foreach ($waContacts as $idx => $wa)
                                                <tr class="hover:bg-gray-50/70 transition">
                                                    <td class="px-6 py-4 text-center">
                                                        <input type="checkbox" name="contacts[{{ $idx }}][number]" value="{{ $wa['number'] }}" class="wa-item-checkbox rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500 cursor-pointer">
                                                        <input type="hidden" name="contacts[{{ $idx }}][name]" value="{{ $wa['name'] }}">
                                                    </td>

                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <div class="h-9 w-9 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs uppercase mr-3 shrink-0">
                                                                {{ strtoupper(substr($wa['name'], 0, 2)) }}
                                                            </div>
                                                            <div>
                                                                <div class="text-sm font-semibold text-gray-900">{{ $wa['name'] }}</div>
                                                                <div class="text-[11px] text-gray-400 font-mono">{{ $wa['jid'] }}</div>
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono font-medium text-gray-800">
                                                        +{{ $wa['number'] }}
                                                    </td>

                                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                        @if($wa['is_saved'])
                                                            <div class="flex items-center gap-1.5">
                                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full">
                                                                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                                    Tersimpan
                                                                </span>
                                                                @if($wa['saved_record'] && $wa['saved_record']->category)
                                                                    <span class="px-2 py-0.5 text-[11px] font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-full">
                                                                        {{ $wa['saved_record']->category }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-gray-500 bg-gray-100 rounded-full">
                                                                Belum Disimpan
                                                            </span>
                                                        @endif
                                                    </td>

                                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                        <div class="flex justify-end items-center gap-2">
                                                            {{-- Tombol Buka Live Chat --}}
                                                            <a href="{{ route('messages.index', ['search' => $wa['number']]) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1.5 rounded-lg transition" title="Buka Live Chat dengan nomor ini">
                                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.65 3.742-.983z"/></svg>
                                                                Chat
                                                            </a>

                                                            {{-- Jika belum tersimpan: Tombol Simpan --}}
                                                            @if(!$wa['is_saved'])
                                                                <button type="button" @click="openSaveModal('{{ addslashes($wa['name']) }}', '{{ $wa['number'] }}')" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1.5 rounded-lg transition">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                                    + Simpan ke Phonebook
                                                                </button>
                                                            @else
                                                                {{-- Jika sudah tersimpan: Link Edit --}}
                                                                @if($wa['saved_record'])
                                                                    <a href="{{ route('phonebook.edit', $wa['saved_record']->id) }}" class="inline-flex items-center text-xs font-semibold text-gray-600 hover:text-indigo-600 px-2 py-1 rounded transition">
                                                                        Edit di Phonebook
                                                                    </a>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-400 bg-gray-50">
                                                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                                    <p class="font-medium text-gray-600">Tidak ada kontak WhatsApp ditemukan</p>
                                                    <p class="text-xs text-gray-400 mt-1">
                                                        @if(!$selectedDevice)
                                                            Silakan pilih device WhatsApp yang aktif terlebih dahulu.
                                                        @elseif($waError)
                                                            Device sedang tidak dapat dihubungi. Klik tombol Sinkronisasi / Refresh untuk mencoba kembali.
                                                        @else
                                                            Device ini tidak memiliki kontak tersimpan atau kata kunci pencarian tidak cocok.
                                                        @endif
                                                    </p>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </form>

                        {{-- Pagination Khusus Tab WhatsApp --}}
                        @if($waContacts && method_exists($waContacts, 'links'))
                            <div class="p-4 border-t border-gray-100 bg-white">
                                {{ $waContacts->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>

        {{-- ======================================================== --}}
        {{-- MODAL 1: SIMPAN 1 KONTAK WA KE PHONEBOOK                --}}
        {{-- ======================================================== --}}
        <div x-show="singleModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="singleModalOpen" @click="singleModalOpen = false" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="singleModalOpen" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                    <form action="{{ route('phonebook.save-wa') }}" method="POST">
                        @csrf
                        <div class="bg-white p-6 sm:p-7">
                            <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                    Simpan ke Buku Telepon SIMAWA
                                </h3>
                                <button type="button" @click="singleModalOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nama Kontak</label>
                                    <input type="text" name="name" x-model="modalName" required class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nomor WhatsApp</label>
                                    <input type="text" name="number" x-model="modalNumber" required readonly class="w-full text-sm border-gray-200 bg-gray-50 text-gray-700 font-mono rounded-lg shadow-sm py-2.5 px-3">
                                    <p class="text-[11px] text-gray-400 mt-1">Nomor otomatis terisi dari akun WhatsApp.</p>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kategori (Opsional)</label>
                                    <input type="text" name="category" x-model="modalCategory" list="categoriesDatalist" placeholder="Contoh: Pelanggan, Mitra, VIP..." class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3">
                                    <datalist id="categoriesDatalist">
                                        @foreach($categories as $c)
                                            <option value="{{ $c }}">
                                        @endforeach
                                    </datalist>
                                    <p class="text-[11px] text-gray-400 mt-1">Pilih dari kategori yang sudah ada atau ketik nama kategori baru.</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row justify-end gap-3 border-t border-gray-100">
                            <button type="button" @click="singleModalOpen = false" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition shadow-sm">
                                Simpan Kontak
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ======================================================== --}}
        {{-- MODAL 2: IMPOR SEMUA KONTAK WA DARI DEVICE              --}}
        {{-- ======================================================== --}}
        @if($selectedDevice)
            <div x-show="importAllModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="importAllModalOpen" @click="importAllModalOpen = false" class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="importAllModalOpen" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                        <form action="{{ route('phonebook.import-all-wa') }}" method="POST">
                            @csrf
                            <input type="hidden" name="device_id" value="{{ $selectedDevice->id }}">

                            <div class="bg-white p-6 sm:p-7">
                                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.65 3.742-.983z"/></svg>
                                        Impor Semua Kontak WhatsApp
                                    </h3>
                                    <button type="button" @click="importAllModalOpen = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                                </div>

                                <div class="space-y-4">
                                    <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-xl text-xs text-emerald-800 leading-relaxed">
                                        <p class="font-bold text-sm text-emerald-900 mb-1">Konfirmasi Impor Massal</p>
                                        Sistem akan mengimpor seluruh <strong>{{ number_format($totalWaContacts) }}</strong> kontak WhatsApp dari device <strong>"{{ $selectedDevice->name }}"</strong> ke Buku Telepon SIMAWA. Kontak yang sudah ada akan diperbarui.
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tetapkan Kategori (Opsional)</label>
                                        <input type="text" name="category" list="allCategoriesDatalist" placeholder="Contoh: Kontak WhatsApp, Pelanggan, dll..." class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 py-2.5 px-3">
                                        <datalist id="allCategoriesDatalist">
                                            @foreach($categories as $c)
                                                <option value="{{ $c }}">
                                            @endforeach
                                        </datalist>
                                        <p class="text-[11px] text-gray-400 mt-1">Kategori ini akan disematkan ke seluruh kontak WhatsApp yang diimpor.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row justify-end gap-3 border-t border-gray-100">
                                <button type="button" @click="importAllModalOpen = false" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                    Batal
                                </button>
                                <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition shadow-sm">
                                    Mulai Impor Semua Kontak
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

    </div>

    {{-- Script JavaScript untuk Checkbox Selection --}}
    <script>
        (function() {
            // 1. Logika Checkbox untuk Tab Phonebook SIMAWA
            function checkBulkDeleteStatus() {
                const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
                const totalCount = document.querySelectorAll('.item-checkbox').length;
                const btnContainer = document.getElementById('bulkDeleteBtnContainer');
                const selectedCountText = document.getElementById('selectedCount');
                const masterCheckbox = document.getElementById('selectAll');

                if (selectedCountText) {
                    selectedCountText.textContent = checkedCount;
                }

                if (checkedCount > 0) {
                    if (btnContainer) btnContainer.classList.remove('hidden');
                } else {
                    if (btnContainer) btnContainer.classList.add('hidden');
                }

                if (masterCheckbox && totalCount > 0) {
                    masterCheckbox.checked = (checkedCount === totalCount);
                }
            }

            // 2. Logika Checkbox untuk Tab WhatsApp
            function checkBulkWaStatus() {
                const checkedCount = document.querySelectorAll('.wa-item-checkbox:checked').length;
                const totalCount = document.querySelectorAll('.wa-item-checkbox').length;
                const toolbar = document.getElementById('bulkWaToolbar');
                const countText = document.getElementById('waSelectedCount');
                const masterCheckbox = document.getElementById('selectAllWa');

                if (countText) {
                    countText.textContent = checkedCount;
                }

                if (checkedCount > 0) {
                    if (toolbar) toolbar.classList.remove('hidden');
                } else {
                    if (toolbar) toolbar.classList.add('hidden');
                }

                if (masterCheckbox && totalCount > 0) {
                    masterCheckbox.checked = (checkedCount === totalCount);
                }
            }

            // Event Delegation Global
            document.addEventListener('change', function(e) {
                // Master Phonebook SIMAWA
                if (e.target && e.target.id === 'selectAll') {
                    const isChecked = e.target.checked;
                    document.querySelectorAll('.item-checkbox').forEach(function(cb) {
                        cb.checked = isChecked;
                    });
                    checkBulkDeleteStatus();
                }
                // Item Phonebook SIMAWA
                else if (e.target && e.target.classList.contains('item-checkbox')) {
                    checkBulkDeleteStatus();
                }

                // Master WhatsApp Checkbox
                else if (e.target && e.target.id === 'selectAllWa') {
                    const isChecked = e.target.checked;
                    document.querySelectorAll('.wa-item-checkbox').forEach(function(cb) {
                        cb.checked = isChecked;
                    });
                    checkBulkWaStatus();
                }
                // Item WhatsApp Checkbox
                else if (e.target && e.target.classList.contains('wa-item-checkbox')) {
                    checkBulkWaStatus();
                }
            });

            // Inisialisasi saat halaman selesai dimuat
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function() {
                    checkBulkDeleteStatus();
                    checkBulkWaStatus();
                });
            } else {
                checkBulkDeleteStatus();
                checkBulkWaStatus();
            }

            window.deleteSingleContact = function(btn) {
                const url = btn.dataset.url;
                const name = btn.dataset.name || 'kontak ini';
                if (!confirm('Apakah Anda yakin ingin menghapus kontak "' + name + '"?')) {
                    return;
                }

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';
                form.appendChild(method);

                document.body.appendChild(form);
                form.submit();
            };
        })();
    </script>
</x-app-layout>
