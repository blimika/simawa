<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Kirim Broadcast Baru') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Kirim pesan massal dengan proteksi anti-blokir jeda acak dan proses di latar belakang.
                </p>
            </div>
            <a href="{{ route('broadcast.index') }}" class="inline-flex items-center gap-2 text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 font-semibold py-2 px-4 rounded-lg text-sm transition shadow-sm">
                &larr; Riwayat Broadcast
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen" x-data="{
        sendMode: 'now',
        scheduledAt: '',
        delayMin: 5,
        delayMax: 10,
        messageText: '',
        setPreset(min, max) {
            this.delayMin = min;
            this.delayMax = max;
        },
        insertTag(tag) {
            const textarea = document.getElementById('messageTextarea');
            if (!textarea) return;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            this.messageText = text.substring(0, start) + tag + text.substring(end);
            this.$nextTick(() => {
                textarea.focus();
                textarea.setSelectionRange(start + tag.length, start + tag.length);
            });
        }
    }">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-300 text-red-800 px-4 py-3.5 rounded-xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            {{-- Kartu Info Anti-Blokir & Background --}}
            <div class="mb-6 bg-indigo-50 border border-indigo-200 p-5 rounded-2xl shadow-sm">
                <div class="flex items-start gap-3.5">
                    <div class="p-2.5 bg-indigo-600 text-white rounded-xl shadow-sm shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-indigo-950 text-base">Sistem Proteksi Anti-Blokir & Scheduler Latar Belakang</h3>
                        <p class="text-xs text-indigo-800 leading-relaxed mt-1">
                            Setelah menekan tombol <strong>Mulai Kirim Broadcast</strong>, seluruh data penerima langsung disimpan ke database dan pengiriman dijalankan secara otomatis di latar belakang dengan <strong>jeda acak (delay)</strong>. Anda <strong>tidak perlu menunggu</strong> dan dapat langsung berpindah halaman dengan aman.
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100">
                <form action="{{ route('broadcast.store') }}" method="POST" id="broadcastForm" class="p-6 sm:p-8 space-y-7">
                    @csrf

                    {{-- 1. Nama Broadcast & Device Pengirim --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">1. Nama Broadcast (Opsional)</label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Pengumuman Layanan November" class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3.5">
                            <p class="text-[11px] text-gray-400 mt-1">Beri nama untuk memudahkan pencarian laporan riwayat broadcast.</p>
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">2. Pilih Device WhatsApp Pengirim <span class="text-red-500">*</span></label>
                            <select name="device_id" required class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3.5">
                                <option value="">-- Pilih Device Terkoneksi --</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}" {{ old('device_id') == $device->id ? 'selected' : '' }}>
                                        {{ $device->name }} ({{ $device->phone_number ? '+' . $device->phone_number : 'No Number' }}) - [Connected]
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-gray-400 mt-1">Hanya device dengan status <strong>Connected</strong> yang dapat mengirim broadcast.</p>
                            @error('device_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- 2. Target Kontak & Mode Pengiriman --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">3. Pilih Grup Kontak Target <span class="text-red-500">*</span></label>
                            <select name="category" required class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2.5 px-3.5">
                                <option value="">-- Pilih Target Kontak --</option>
                                <option value="all" {{ old('category') == 'all' ? 'selected' : '' }} class="font-bold text-indigo-700">
                                    » Kirim ke SEMUA Kontak Phonebook ({{ number_format($totalContactsCount) }} Kontak) «
                                </option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>
                                        Grup: {{ $cat }} ({{ number_format($categoryCounts[$cat] ?? 0) }} Kontak)
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-gray-400 mt-1">Pesan akan dikirimkan ke setiap nomor yang ada di dalam grup target tersebut.</p>
                            @error('category') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">4. Jadwal Pengiriman <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 gap-3 mb-2">
                                <label class="border rounded-xl p-3 flex items-center gap-2.5 cursor-pointer transition" :class="sendMode === 'now' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-900 font-bold' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                    <input type="radio" name="send_mode" value="now" x-model="sendMode" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs">Kirim Sekarang</span>
                                </label>
                                <label class="border rounded-xl p-3 flex items-center gap-2.5 cursor-pointer transition" :class="sendMode === 'scheduled' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-900 font-bold' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                    <input type="radio" name="send_mode" value="scheduled" x-model="sendMode" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-xs">Jadwalkan</span>
                                </label>
                            </div>

                            <div x-show="sendMode === 'scheduled'" x-cloak class="mt-2">
                                <input type="datetime-local" name="scheduled_at" x-model="scheduledAt" class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-2 px-3">
                                <p class="text-[11px] text-purple-700 mt-1">Pesan akan otomatis mulai dikirim saat memasuki waktu yang dijadwalkan.</p>
                            </div>
                            @error('scheduled_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- 3. Pengaturan Jeda Pengiriman (Anti-Blokir WhatsApp) --}}
                    <div class="p-5 bg-amber-50/60 rounded-2xl border border-amber-200/80">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-3">
                            <div>
                                <label class="block text-xs font-bold text-amber-950 uppercase">
                                    5. Pengaturan Jeda Pesan (Anti-Blokir WhatsApp)
                                </label>
                                <p class="text-xs text-amber-800 mt-0.5">
                                    Sistem akan memberikan jeda acak di antara setiap pesan terkirim untuk menyerupai perilaku manusia asli.
                                </p>
                            </div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-[11px] font-semibold text-amber-900 mr-1">Preset:</span>
                                <button type="button" @click="setPreset(5, 10)" class="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-white border border-amber-300 text-amber-900 hover:bg-amber-100 transition shadow-xs">
                                    Rekomendasi (5-10s)
                                </button>
                                <button type="button" @click="setPreset(10, 20)" class="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-white border border-amber-300 text-amber-900 hover:bg-amber-100 transition shadow-xs">
                                    Ekstra Aman (10-20s)
                                </button>
                                <button type="button" @click="setPreset(3, 6)" class="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-white border border-amber-300 text-amber-900 hover:bg-amber-100 transition shadow-xs">
                                    Cepat (3-6s)
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">Jeda Minimal (Detik)</label>
                                <div class="relative">
                                    <input type="number" name="delay_min" x-model="delayMin" min="1" max="300" required class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:ring-amber-500 focus:border-amber-500 py-2 px-3">
                                    <span class="absolute right-3 top-2.5 text-xs text-gray-400 font-medium">detik</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 uppercase mb-1">Jeda Maksimal (Detik)</label>
                                <div class="relative">
                                    <input type="number" name="delay_max" x-model="delayMax" min="1" max="300" required class="w-full text-sm border-gray-300 rounded-xl shadow-sm focus:ring-amber-500 focus:border-amber-500 py-2 px-3">
                                    <span class="absolute right-3 top-2.5 text-xs text-gray-400 font-medium">detik</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Isi Pesan Broadcast & Variabel Personalisasi --}}
                    <div>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-2">
                            <label class="block text-xs font-bold text-gray-700 uppercase">
                                6. Isi Pesan Broadcast <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-xs text-gray-500">Sisipkan Variabel:</span>
                                <button type="button" @click="insertTag('{nama}')" class="text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 px-2.5 py-1 rounded-md transition" title="Sisipkan nama penerima dari kontak">
                                    + {nama}
                                </button>
                                <button type="button" @click="insertTag('{nomor}')" class="text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 px-2.5 py-1 rounded-md transition" title="Sisipkan nomor WhatsApp penerima">
                                    + {nomor}
                                </button>
                            </div>
                        </div>

                        <textarea id="messageTextarea" name="message" x-model="messageText" rows="6" required class="w-full text-sm border-gray-300 rounded-2xl shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-4" placeholder="Ketik pengumuman atau pesan massal Anda di sini... Gunakan {nama} untuk menyebut nama kontak secara personal."></textarea>
                        @error('message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                        {{-- Pratinjau Pesan --}}
                        <div x-show="messageText.trim().length > 0" class="mt-3 p-4 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1">Pratinjau Pesan Contoh (Penerima: Bapak/Ibu Budi Santoso):</div>
                            <div class="text-sm text-gray-800 whitespace-pre-wrap font-sans bg-white p-3 rounded-lg border border-gray-100 shadow-xs" x-text="messageText.replace(/{nama}|{name}/gi, 'Budi Santoso').replace(/{nomor}|{phone}/gi, '081234567890')"></div>
                        </div>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="pt-4 border-t border-gray-100">
                        <button type="submit" id="btnSubmit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-6 rounded-xl shadow-md transition text-base flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            <span x-text="sendMode === 'scheduled' ? 'Jadwalkan Pengiriman Broadcast' : '🚀 Mulai Kirim Broadcast (Latar Belakang)'"></span>
                        </button>
                        <p class="text-center text-xs text-gray-400 mt-2">
                            Pesan langsung diproses secara asinkron. Anda dapat memantau status setiap kontak di halaman laporan.
                        </p>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>
