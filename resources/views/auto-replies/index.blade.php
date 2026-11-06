<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Pesan Otomatis Live Chat') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">
                    Sesuaikan salam pembuka, pesan saat agen mulai melayani, dan salam penutup saat live chat selesai.
                </p>
            </div>
            @if($devices->count() > 1 && $selectedDevice)
                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm">
                    <label for="device_selector" class="text-xs font-semibold text-gray-600 whitespace-nowrap">Pilih Device:</label>
                    <select id="device_selector" onchange="window.location.href = '{{ route('auto-replies.index') }}?device_id=' + this.value" class="text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1 pl-2 pr-6 font-medium text-gray-800 cursor-pointer">
                        @foreach($devices as $dev)
                            <option value="{{ $dev->id }}" {{ $selectedDevice->id == $dev->id ? 'selected' : '' }}>
                                {{ $dev->name }} ({{ $dev->phone_number }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </x-slot>

    <div class="py-10 bg-gray-50">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Alert Notifikasi -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative shadow-sm flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative shadow-sm flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(!$selectedDevice)
                <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100 text-center">
                    <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-1">Belum Ada Device Terdaftar</h3>
                    <p class="text-sm text-gray-500 mb-4 max-w-md mx-auto">Anda perlu mendaftarkan dan menghubungkan device WhatsApp terlebih dahulu sebelum dapat mengatur pesan otomatis.</p>
                    <a href="{{ route('devices.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-lg shadow-sm transition">
                        + Tambah Device Sekarang
                    </a>
                </div>
            @else

                <!-- Kotak Panduan Variabel Dinamis -->
                <div class="bg-gradient-to-r from-indigo-50 to-blue-50 border border-indigo-100 p-5 rounded-xl shadow-sm">
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
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">
                                    1
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 text-base">Salam Pembuka (Pesan Baru / Tiket Baru)</h3>
                                    <p class="text-xs text-gray-500">Dikirim otomatis ke pelanggan saat pertama kali mengirim pesan atau saat tiket baru terbit.</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                Pesan Masuk Pertama
                            </span>
                        </div>

                        <div class="p-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
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
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-2">Simulasi Tampilan WhatsApp</span>
                                    <div class="bg-white rounded-lg rounded-tl-none p-3 shadow-sm text-xs text-gray-800 leading-relaxed whitespace-pre-wrap relative max-w-sm" id="preview_greeting">
                                    </div>
                                </div>
                                <div class="text-[10px] text-gray-400 text-right mt-3 font-mono">
                                    Otomatis dikirim oleh Bot Layanan
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- CARD 2: PESAN DIAMBIL AGEN (KLAIM TIKET)       -->
                    <!-- ============================================== -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-sm">
                                    2
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 text-base">Pesan Diambil Agen (Mulai Pelayanan)</h3>
                                    <p class="text-xs text-gray-500">Dikirim otomatis ke pelanggan saat operator/agen mengeklik tombol "Layani Pelanggan".</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                Saat Agen Klaim Tiket
                            </span>
                        </div>

                        <div class="p-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
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
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-2">Simulasi Tampilan WhatsApp</span>
                                    <div class="bg-[#dcf8c6] rounded-lg rounded-tr-none p-3 shadow-sm text-xs text-gray-800 leading-relaxed whitespace-pre-wrap relative max-w-sm ml-auto" id="preview_claim">
                                    </div>
                                </div>
                                <div class="text-[10px] text-gray-400 text-right mt-3 font-mono">
                                    Dikirim atas nama Agen yang mengeklik
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================== -->
                    <!-- CARD 3: SALAM PENUTUP (TIKET SELESAI)          -->
                    <!-- ============================================== -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-sm">
                                    3
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 text-base">Salam Penutup (Live Chat Selesai)</h3>
                                    <p class="text-xs text-gray-500">Dikirim otomatis ke pelanggan saat operator/agen mengeklik tombol "Tutup Tiket Selesai".</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Saat Tiket Ditutup
                            </span>
                        </div>

                        <div class="p-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
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
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 block mb-2">Simulasi Tampilan WhatsApp</span>
                                    <div class="bg-white rounded-lg rounded-tl-none p-3 shadow-sm text-xs text-gray-800 leading-relaxed whitespace-pre-wrap relative max-w-sm" id="preview_closing">
                                    </div>
                                </div>
                                <div class="text-[10px] text-gray-400 text-right mt-3 font-mono">
                                    Otomatis dikirim saat tiket ditutup
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action Bar -->
                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4">
                        @if($devices->count() > 1)
                            <label class="inline-flex items-center cursor-pointer select-none">
                                <input type="checkbox" name="apply_to_all" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-xs font-semibold text-gray-700">Terapkan perubahan ini ke SEMUA ({{ $devices->count() }}) Device WhatsApp saya</span>
                            </label>
                        @else
                            <div class="text-xs text-gray-500">
                                Konfigurasi ini aktif untuk device: <strong class="text-gray-800">{{ $selectedDevice->name }}</strong>
                            </div>
                        @endif

                        <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Simpan Pesan Otomatis
                            </button>
                        </div>
                    </div>

                </form>

                <!-- Form Reset Khusus -->
                <form id="resetForm" action="{{ route('auto-replies.reset', $selectedDevice->id) }}" method="POST" class="hidden">
                    @csrf
                </form>

            @endif

        </div>
    </div>

    <!-- Script JavaScript untuk Real-time Preview & Sisipkan Tag -->
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
