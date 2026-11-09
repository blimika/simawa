<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                        {{ __('Pesan Otomatis Live Chat') }}
                    </h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 border border-indigo-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 mr-1.5 animate-pulse"></span>
                        Auto-Responder
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    Sesuaikan salam pembuka, pesan saat agen mulai melayani, dan salam penutup saat live chat selesai.
                </p>
            </div>

            @if($devices->count() > 1 && $selectedDevice)
                <div class="flex items-center gap-2.5 bg-white px-3.5 py-2 rounded-xl border border-gray-200 shadow-sm">
                    <label for="device_selector" class="text-xs font-bold text-gray-600 uppercase tracking-wider whitespace-nowrap">Pilih Device:</label>
                    <select id="device_selector" onchange="window.location.href = '{{ route('auto-replies.index') }}?device_id=' + this.value" class="text-xs border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1.5 pl-2.5 pr-7 font-bold text-gray-800 cursor-pointer">
                        @foreach($devices as $dev)
                            <option value="{{ $dev->id }}" {{ $selectedDevice->id == $dev->id ? 'selected' : '' }}>
                                {{ $dev->name }} ({{ $dev->phone_number ? '+'.$dev->phone_number : 'Slot: '.$dev->gowa_session_id }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Alert Notifikasi Flash -->
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3.5 rounded-xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3.5 rounded-xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">&times;</button>
                </div>
            @endif

            @if(!$selectedDevice)
                <div class="bg-white p-10 rounded-2xl shadow-sm border border-gray-100 text-center">
                    <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-1">Belum Ada Device Terdaftar</h3>
                    <p class="text-sm text-gray-500 mb-5 max-w-md mx-auto">Anda perlu mendaftarkan dan menghubungkan device WhatsApp terlebih dahulu sebelum dapat mengatur pesan otomatis.</p>
                    <a href="{{ route('devices.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-sm transition">
                        + Tambah Device Sekarang
                    </a>
                </div>
            @else

                <!-- Peringatan Jika Mode Live Chat Non-Aktif pada Device Ini -->
                @if(!$selectedDevice->is_livechat_enabled)
                    <div class="bg-amber-50 border border-amber-300 text-amber-900 p-4 rounded-xl shadow-sm flex items-start gap-3.5">
                        <div class="p-2 bg-amber-100 text-amber-700 rounded-lg shrink-0 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-sm text-amber-950">Mode Live Chat Saat Ini Non-Aktif (Hanya Forward Webhook)</h4>
                            <p class="text-xs text-amber-800 mt-0.5">
                                Device <b>{{ $selectedDevice->name }}</b> saat ini diatur dalam mode <i>Forward Webhook Saja</i>. Pesan otomatis di bawah ini baru akan bekerja secara otomatis jika fitur <b>Live Chat</b> diaktifkan pada pengaturan device.
                            </p>
                            <div class="mt-2.5">
                                <a href="{{ route('devices.edit', $selectedDevice->id) }}" class="inline-flex items-center gap-1 text-xs font-bold text-amber-900 bg-amber-200/80 hover:bg-amber-200 px-3 py-1.5 rounded-lg transition">
                                    Aktifkan Live Chat di Pengaturan Device &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Ringkasan Status Switch Fitur Auto-Reply Device -->
                <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2">
                                <span>⚡</span> Kontrol Mandiri Fitur Pesan Otomatis
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Setiap pesan otomatis dapat diaktifkan atau dinonaktifkan secara independen. Anda dapat mengaktifkan ketiganya, mematikan seluruhnya, atau memilih hanya 1-2 fitur saja sesuai kebutuhan.
                            </p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            {{-- Badge Status Salam Pembuka --}}
                            <span id="badge_summary_greeting" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ ($selectedDevice->is_greeting_enabled ?? true) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                                <span class="w-2 h-2 rounded-full {{ ($selectedDevice->is_greeting_enabled ?? true) ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                Salam Pembuka: <b>{{ ($selectedDevice->is_greeting_enabled ?? true) ? 'Aktif' : 'Mati' }}</b>
                            </span>

                            {{-- Badge Status Pesan Diambil --}}
                            <span id="badge_summary_claim" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ ($selectedDevice->is_claim_enabled ?? true) ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                                <span class="w-2 h-2 rounded-full {{ ($selectedDevice->is_claim_enabled ?? true) ? 'bg-amber-500' : 'bg-gray-400' }}"></span>
                                Diambil Agen: <b>{{ ($selectedDevice->is_claim_enabled ?? true) ? 'Aktif' : 'Mati' }}</b>
                            </span>

                            {{-- Badge Status Salam Penutup --}}
                            <span id="badge_summary_closing" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ ($selectedDevice->is_closing_enabled ?? true) ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                                <span class="w-2 h-2 rounded-full {{ ($selectedDevice->is_closing_enabled ?? true) ? 'bg-blue-500' : 'bg-gray-400' }}"></span>
                                Salam Penutup: <b>{{ ($selectedDevice->is_closing_enabled ?? true) ? 'Aktif' : 'Mati' }}</b>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Kotak Panduan Variabel Dinamis -->
                <div class="bg-gradient-to-r from-indigo-50 via-blue-50 to-indigo-50 border border-indigo-100 p-5 rounded-xl shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-indigo-100 text-indigo-700 rounded-lg shrink-0 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-bold text-indigo-900">Variabel Dinamis yang Dapat Disisipkan</h3>
                            <p class="text-xs text-indigo-700 mt-0.5 mb-3">Teks di dalam kurung kurawal <code class="font-mono bg-white/70 px-1 py-0.5 rounded text-indigo-900">{...}</code> akan otomatis diganti dengan data asli pelanggan saat pesan dikirimkan.</p>
                            <div class="flex flex-wrap gap-2 text-xs">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-white border border-indigo-200 text-indigo-800 font-mono font-medium shadow-2xs">
                                    <strong class="text-indigo-600">{nama}</strong> &rarr; Nama pelanggan
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-white border border-indigo-200 text-indigo-800 font-mono font-medium shadow-2xs">
                                    <strong class="text-indigo-600">{nomor_tiket}</strong> &rarr; Nomor tiket layanan
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-white border border-indigo-200 text-indigo-800 font-mono font-medium shadow-2xs">
                                    <strong class="text-indigo-600">{device}</strong> &rarr; Nama device instansi
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-white border border-indigo-200 text-indigo-800 font-mono font-medium shadow-2xs">
                                    <strong class="text-indigo-600">{agen}</strong> &rarr; Nama agen/operator
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Utama Pengaturan Pesan -->
                <form action="{{ route('auto-replies.update', $selectedDevice->id) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- ============================================== -->
                    <!-- CARD 1: SALAM PEMBUKA (TIKET BARU)            -->
                    <!-- ============================================== -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden transition-all duration-200" id="card_greeting">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                                    1
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 text-base">Salam Pembuka (Pesan Baru / Tiket Baru)</h3>
                                    <p class="text-xs text-gray-500">Dikirim otomatis ke WhatsApp pelanggan saat pertama kali mengirim pesan atau saat tiket baru dibuat.</p>
                                </div>
                            </div>

                            {{-- TOGGLE ON / OFF SALAM PEMBUKA --}}
                            <div class="flex items-center gap-3 self-end sm:self-auto">
                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" 
                                           name="is_greeting_enabled" 
                                           value="1" 
                                           id="toggle_greeting" 
                                           class="sr-only peer" 
                                           {{ old('is_greeting_enabled', $selectedDevice->is_greeting_enabled ?? true) ? 'checked' : '' }}
                                           onchange="handleToggleChange('greeting')">
                                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                    <span id="label_toggle_greeting" class="ml-2.5 text-xs font-bold {{ old('is_greeting_enabled', $selectedDevice->is_greeting_enabled ?? true) ? 'text-emerald-700' : 'text-gray-400' }}">
                                        {{ old('is_greeting_enabled', $selectedDevice->is_greeting_enabled ?? true) ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="p-6">
                            {{-- Banner Peringatan Jika Nonaktif --}}
                            <div id="notice_greeting" class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 flex items-center gap-2 {{ old('is_greeting_enabled', $selectedDevice->is_greeting_enabled ?? true) ? 'hidden' : '' }}">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>Fitur <b>Salam Pembuka</b> sedang dinonaktifkan. Saat pesan baru masuk, tiket tetap dibuat namun pelanggan <b>tidak</b> akan menerima pesan pembuka otomatis dari bot.</span>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                                <!-- Kolom Editor Textarea (7 kolom) -->
                                <div class="lg:col-span-7 space-y-2">
                                    <div class="flex justify-between items-center">
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide">Template Teks Salam Pembuka</label>
                                        <div class="flex gap-1 text-[11px]">
                                            <button type="button" onclick="insertTag('greeting_message', '{nama}')" class="px-1.5 py-0.5 rounded bg-gray-100 hover:bg-indigo-100 hover:text-indigo-700 font-mono transition">+ {nama}</button>
                                            <button type="button" onclick="insertTag('greeting_message', '{nomor_tiket}')" class="px-1.5 py-0.5 rounded bg-gray-100 hover:bg-indigo-100 hover:text-indigo-700 font-mono transition">+ {nomor_tiket}</button>
                                            <button type="button" onclick="insertTag('greeting_message', '{device}')" class="px-1.5 py-0.5 rounded bg-gray-100 hover:bg-indigo-100 hover:text-indigo-700 font-mono transition">+ {device}</button>
                                        </div>
                                    </div>

                                    <textarea id="greeting_message" name="greeting_message" rows="7" 
                                        class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-sans leading-relaxed"
                                        oninput="updatePreview('greeting_message', 'preview_greeting')"
                                        placeholder="Ketik pesan pembuka...">{{ old('greeting_message', $selectedDevice->greeting_message ?? \App\Models\Device::DEFAULT_GREETING_MESSAGE) }}</textarea>

                                    <div class="flex justify-between items-center text-xs text-gray-400">
                                        <span>Gunakan format WhatsApp standar: *tebal*, _miring_.</span>
                                        <button type="button" onclick="resetToDefault('greeting_message', defaultGreeting)" class="text-indigo-600 hover:underline font-medium">
                                            Pulihkan Teks Default
                                        </button>
                                    </div>
                                </div>

                                <!-- Kolom Simulasi WhatsApp Preview (5 kolom) -->
                                <div class="lg:col-span-5 bg-[#efeae2] p-4 rounded-xl border border-gray-200 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Simulasi Tampilan WhatsApp</span>
                                            <span id="badge_preview_greeting" class="text-[10px] font-bold px-2 py-0.5 rounded {{ old('is_greeting_enabled', $selectedDevice->is_greeting_enabled ?? true) ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-600' }}">
                                                {{ old('is_greeting_enabled', $selectedDevice->is_greeting_enabled ?? true) ? 'Aktif Terkirim' : 'Tidak Terkirim (Mati)' }}
                                            </span>
                                        </div>
                                        <div class="bg-white rounded-lg rounded-tl-none p-3 shadow-sm text-xs text-gray-800 leading-relaxed whitespace-pre-wrap relative max-w-sm" id="preview_greeting">
                                        </div>
                                    </div>
                                    <div class="text-[10px] text-gray-400 text-right mt-3 font-mono">
                                        Otomatis dikirim oleh Bot Layanan SIMAWA
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- CARD 2: PESAN DIAMBIL AGEN (KLAIM TIKET)       -->
                    <!-- ============================================== -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden transition-all duration-200" id="card_claim">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-sm">
                                    2
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 text-base">Pesan Diambil Agen (Mulai Pelayanan)</h3>
                                    <p class="text-xs text-gray-500">Dikirim otomatis ke WhatsApp pelanggan saat operator/agen mengeklik tombol "Layani Pelanggan".</p>
                                </div>
                            </div>

                            {{-- TOGGLE ON / OFF PESAN DIAMBIL AGEN --}}
                            <div class="flex items-center gap-3 self-end sm:self-auto">
                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" 
                                           name="is_claim_enabled" 
                                           value="1" 
                                           id="toggle_claim" 
                                           class="sr-only peer" 
                                           {{ old('is_claim_enabled', $selectedDevice->is_claim_enabled ?? true) ? 'checked' : '' }}
                                           onchange="handleToggleChange('claim')">
                                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                    <span id="label_toggle_claim" class="ml-2.5 text-xs font-bold {{ old('is_claim_enabled', $selectedDevice->is_claim_enabled ?? true) ? 'text-emerald-700' : 'text-gray-400' }}">
                                        {{ old('is_claim_enabled', $selectedDevice->is_claim_enabled ?? true) ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="p-6">
                            {{-- Banner Peringatan Jika Nonaktif --}}
                            <div id="notice_claim" class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 flex items-center gap-2 {{ old('is_claim_enabled', $selectedDevice->is_claim_enabled ?? true) ? 'hidden' : '' }}">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>Fitur <b>Pesan Diambil Agen</b> sedang dinonaktifkan. Agen akan mengklaim tiket seperti biasa tanpa mengirimkan notifikasi teks otomatis ke WhatsApp pelanggan.</span>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                                <!-- Kolom Editor Textarea (7 kolom) -->
                                <div class="lg:col-span-7 space-y-2">
                                    <div class="flex justify-between items-center">
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide">Template Teks Agen Mulai Melayani</label>
                                        <div class="flex gap-1 text-[11px]">
                                            <button type="button" onclick="insertTag('claim_message', '{agen}')" class="px-1.5 py-0.5 rounded bg-gray-100 hover:bg-amber-100 hover:text-amber-800 font-mono transition">+ {agen}</button>
                                            <button type="button" onclick="insertTag('claim_message', '{nama}')" class="px-1.5 py-0.5 rounded bg-gray-100 hover:bg-amber-100 hover:text-amber-800 font-mono transition">+ {nama}</button>
                                            <button type="button" onclick="insertTag('claim_message', '{device}')" class="px-1.5 py-0.5 rounded bg-gray-100 hover:bg-amber-100 hover:text-amber-800 font-mono transition">+ {device}</button>
                                        </div>
                                    </div>

                                    <textarea id="claim_message" name="claim_message" rows="4" 
                                        class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500 font-sans leading-relaxed"
                                        oninput="updatePreview('claim_message', 'preview_claim')"
                                        placeholder="Ketik pesan klaim tiket...">{{ old('claim_message', $selectedDevice->claim_message ?? \App\Models\Device::DEFAULT_CLAIM_MESSAGE) }}</textarea>

                                    <div class="flex justify-between items-center text-xs text-gray-400">
                                        <span>Gunakan format WhatsApp standar: *tebal*, _miring_.</span>
                                        <button type="button" onclick="resetToDefault('claim_message', defaultClaim)" class="text-amber-600 hover:underline font-medium">
                                            Pulihkan Teks Default
                                        </button>
                                    </div>
                                </div>

                                <!-- Kolom Simulasi WhatsApp Preview (5 kolom) -->
                                <div class="lg:col-span-5 bg-[#efeae2] p-4 rounded-xl border border-gray-200 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Simulasi Tampilan WhatsApp</span>
                                            <span id="badge_preview_claim" class="text-[10px] font-bold px-2 py-0.5 rounded {{ old('is_claim_enabled', $selectedDevice->is_claim_enabled ?? true) ? 'bg-amber-100 text-amber-800' : 'bg-gray-200 text-gray-600' }}">
                                                {{ old('is_claim_enabled', $selectedDevice->is_claim_enabled ?? true) ? 'Aktif Terkirim' : 'Tidak Terkirim (Mati)' }}
                                            </span>
                                        </div>
                                        <div class="bg-[#dcf8c6] rounded-lg rounded-tr-none p-3 shadow-sm text-xs text-gray-800 leading-relaxed whitespace-pre-wrap relative max-w-sm ml-auto" id="preview_claim">
                                        </div>
                                    </div>
                                    <div class="text-[10px] text-gray-400 text-right mt-3 font-mono">
                                        Dikirim atas nama Agen yang mengeklik
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- CARD 3: SALAM PENUTUP (TIKET SELESAI)          -->
                    <!-- ============================================== -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden transition-all duration-200" id="card_closing">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-sm">
                                    3
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 text-base">Salam Penutup (Live Chat Selesai)</h3>
                                    <p class="text-xs text-gray-500">Dikirim otomatis ke WhatsApp pelanggan saat operator/agen mengeklik tombol "Tutup Tiket Selesai".</p>
                                </div>
                            </div>

                            {{-- TOGGLE ON / OFF SALAM PENUTUP --}}
                            <div class="flex items-center gap-3 self-end sm:self-auto">
                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" 
                                           name="is_closing_enabled" 
                                           value="1" 
                                           id="toggle_closing" 
                                           class="sr-only peer" 
                                           {{ old('is_closing_enabled', $selectedDevice->is_closing_enabled ?? true) ? 'checked' : '' }}
                                           onchange="handleToggleChange('closing')">
                                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                    <span id="label_toggle_closing" class="ml-2.5 text-xs font-bold {{ old('is_closing_enabled', $selectedDevice->is_closing_enabled ?? true) ? 'text-emerald-700' : 'text-gray-400' }}">
                                        {{ old('is_closing_enabled', $selectedDevice->is_closing_enabled ?? true) ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="p-6">
                            {{-- Banner Peringatan Jika Nonaktif --}}
                            <div id="notice_closing" class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800 flex items-center gap-2 {{ old('is_closing_enabled', $selectedDevice->is_closing_enabled ?? true) ? 'hidden' : '' }}">
                                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>Fitur <b>Salam Penutup</b> sedang dinonaktifkan. Saat agen menandai tiket selesai, tiket tetap ditutup namun pelanggan <b>tidak</b> akan menerima pesan penutup otomatis dari bot.</span>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                                <!-- Kolom Editor Textarea (7 kolom) -->
                                <div class="lg:col-span-7 space-y-2">
                                    <div class="flex justify-between items-center">
                                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide">Template Teks Salam Penutup</label>
                                        <div class="flex gap-1 text-[11px]">
                                            <button type="button" onclick="insertTag('closing_message', '{nama}')" class="px-1.5 py-0.5 rounded bg-gray-100 hover:bg-emerald-100 hover:text-emerald-800 font-mono transition">+ {nama}</button>
                                            <button type="button" onclick="insertTag('closing_message', '{nomor_tiket}')" class="px-1.5 py-0.5 rounded bg-gray-100 hover:bg-emerald-100 hover:text-emerald-800 font-mono transition">+ {nomor_tiket}</button>
                                            <button type="button" onclick="insertTag('closing_message', '{device}')" class="px-1.5 py-0.5 rounded bg-gray-100 hover:bg-emerald-100 hover:text-emerald-800 font-mono transition">+ {device}</button>
                                        </div>
                                    </div>

                                    <textarea id="closing_message" name="closing_message" rows="7" 
                                        class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-emerald-500 focus:ring-emerald-500 font-sans leading-relaxed"
                                        oninput="updatePreview('closing_message', 'preview_closing')"
                                        placeholder="Ketik pesan penutup...">{{ old('closing_message', $selectedDevice->closing_message ?? \App\Models\Device::DEFAULT_CLOSING_MESSAGE) }}</textarea>

                                    <div class="flex justify-between items-center text-xs text-gray-400">
                                        <span>Gunakan format WhatsApp standar: *tebal*, _miring_.</span>
                                        <button type="button" onclick="resetToDefault('closing_message', defaultClosing)" class="text-emerald-600 hover:underline font-medium">
                                            Pulihkan Teks Default
                                        </button>
                                    </div>
                                </div>

                                <!-- Kolom Simulasi WhatsApp Preview (5 kolom) -->
                                <div class="lg:col-span-5 bg-[#efeae2] p-4 rounded-xl border border-gray-200 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Simulasi Tampilan WhatsApp</span>
                                            <span id="badge_preview_closing" class="text-[10px] font-bold px-2 py-0.5 rounded {{ old('is_closing_enabled', $selectedDevice->is_closing_enabled ?? true) ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-600' }}">
                                                {{ old('is_closing_enabled', $selectedDevice->is_closing_enabled ?? true) ? 'Aktif Terkirim' : 'Tidak Terkirim (Mati)' }}
                                            </span>
                                        </div>
                                        <div class="bg-white rounded-lg rounded-tl-none p-3 shadow-sm text-xs text-gray-800 leading-relaxed whitespace-pre-wrap relative max-w-sm" id="preview_closing">
                                        </div>
                                    </div>
                                    <div class="text-[10px] text-gray-400 text-right mt-3 font-mono">
                                        Otomatis dikirim saat tiket ditutup
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action Bar -->
                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4">
                        @if($devices->count() > 1)
                            <label class="inline-flex items-center cursor-pointer select-none">
                                <input type="checkbox" name="apply_to_all" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-xs font-semibold text-gray-700">Terapkan konfigurasi pesan &amp; toggle ini ke SEMUA ({{ $devices->count() }}) Device WhatsApp saya</span>
                            </label>
                        @else
                            <div class="text-xs text-gray-500">
                                Konfigurasi ini aktif untuk device: <strong class="text-gray-800">{{ $selectedDevice->name }}</strong>
                            </div>
                        @endif

                        <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Simpan Pengaturan Pesan
                            </button>
                        </div>
                    </div>

                </form>

            @endif

        </div>
    </div>

    <!-- Script JavaScript untuk Real-time Preview, Sisipkan Tag & Toggle Status -->
    <script>
        const defaultGreeting = @json(\App\Models\Device::DEFAULT_GREETING_MESSAGE);
        const defaultClaim = @json(\App\Models\Device::DEFAULT_CLAIM_MESSAGE);
        const defaultClosing = @json(\App\Models\Device::DEFAULT_CLOSING_MESSAGE);

        const dummyValues = {
            nama: 'Bapak/Ibu Budi',
            customer_name: 'Bapak/Ibu Budi',
            device: '{{ $selectedDevice->name ?? "SIMAWA" }}',
            device_name: '{{ $selectedDevice->name ?? "SIMAWA" }}',
            nomor_tiket: 'LYN-260506-ABCD',
            ticket_number: 'LYN-260506-ABCD',
            tiket: 'LYN-260506-ABCD',
            agen: '{{ auth()->user()->name }}',
            agent_name: '{{ auth()->user()->name }}'
        };

        function handleToggleChange(type) {
            const toggle = document.getElementById('toggle_' + type);
            const label = document.getElementById('label_toggle_' + type);
            const notice = document.getElementById('notice_' + type);
            const badgePreview = document.getElementById('badge_preview_' + type);
            const badgeSummary = document.getElementById('badge_summary_' + type);

            const isChecked = toggle ? toggle.checked : false;

            if (label) {
                label.textContent = isChecked ? 'Aktif' : 'Nonaktif';
                label.className = 'ml-2.5 text-xs font-bold ' + (isChecked ? 'text-emerald-700' : 'text-gray-400');
            }

            if (notice) {
                if (isChecked) {
                    notice.classList.add('hidden');
                } else {
                    notice.classList.remove('hidden');
                }
            }

            if (badgePreview) {
                badgePreview.textContent = isChecked ? 'Aktif Terkirim' : 'Tidak Terkirim (Mati)';
                badgePreview.className = 'text-[10px] font-bold px-2 py-0.5 rounded ' + (isChecked ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-200 text-gray-600');
            }

            if (badgeSummary) {
                const titleMap = {
                    greeting: 'Salam Pembuka',
                    claim: 'Diambil Agen',
                    closing: 'Salam Penutup'
                };
                const colorMap = {
                    greeting: 'emerald',
                    claim: 'amber',
                    closing: 'blue'
                };
                const c = colorMap[type] || 'indigo';
                if (isChecked) {
                    badgeSummary.className = `inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-${c}-50 text-${c}-700 border border-${c}-200`;
                    badgeSummary.innerHTML = `<span class="w-2 h-2 rounded-full bg-${c}-500"></span>${titleMap[type]}: <b>Aktif</b>`;
                } else {
                    badgeSummary.className = 'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500 border border-gray-200';
                    badgeSummary.innerHTML = `<span class="w-2 h-2 rounded-full bg-gray-400"></span>${titleMap[type]}: <b>Mati</b>`;
                }
            }
        }

        function updatePreview(textareaId, previewId) {
            const textarea = document.getElementById(textareaId);
            const preview = document.getElementById(previewId);
            if (!textarea || !preview) return;

            let text = textarea.value;
            if (!text.trim()) {
                preview.textContent = '(Pesan kosong / dinonaktifkan)';
                preview.classList.add('italic', 'text-gray-400');
                return;
            }
            preview.classList.remove('italic', 'text-gray-400');

            // Ganti placeholder dengan nilai dummy
            for (const [key, val] of Object.entries(dummyValues)) {
                const regexLower = new RegExp('{' + key + '}', 'gi');
                text = text.replace(regexLower, val);
            }

            preview.textContent = text;
        }

        function insertTag(textareaId, tag) {
            const textarea = document.getElementById(textareaId);
            if (!textarea) return;

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const current = textarea.value;

            textarea.value = current.substring(0, start) + tag + current.substring(end);
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = start + tag.length;

            if (textareaId === 'greeting_message') updatePreview('greeting_message', 'preview_greeting');
            if (textareaId === 'claim_message') updatePreview('claim_message', 'preview_claim');
            if (textareaId === 'closing_message') updatePreview('closing_message', 'preview_closing');
        }

        function resetToDefault(textareaId, defaultText) {
            if (!confirm('Pulihkan teks template ini ke teks default bawaan sistem?')) return;
            const textarea = document.getElementById(textareaId);
            if (textarea) {
                textarea.value = defaultText;
                if (textareaId === 'greeting_message') updatePreview('greeting_message', 'preview_greeting');
                if (textareaId === 'claim_message') updatePreview('claim_message', 'preview_claim');
                if (textareaId === 'closing_message') updatePreview('closing_message', 'preview_closing');
            }
        }

        // Jalankan initial preview saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            updatePreview('greeting_message', 'preview_greeting');
            updatePreview('claim_message', 'preview_claim');
            updatePreview('closing_message', 'preview_closing');
        });
    </script>
</x-app-layout>
