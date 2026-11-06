<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Device WhatsApp') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">

                <form action="{{ route('devices.update', $device->id) }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    @if($device->status === 'connected')
                        <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm">
                            <div class="flex items-center gap-3.5">
                                <div class="relative shrink-0">
                                    @if(!empty($device->whatsapp_avatar))
                                        <img src="{{ $device->whatsapp_avatar }}" alt="{{ $device->whatsapp_name }}" class="w-12 h-12 rounded-full object-cover border-2 border-emerald-400 shadow-sm" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 font-bold text-sm uppercase items-center justify-center border-2 border-emerald-300 shadow-sm" style="display: none;">
                                            {{ strtoupper(substr($device->whatsapp_name ?: ($device->phone_number ?: 'WA'), 0, 2)) }}
                                        </div>
                                    @else
                                        <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm uppercase border-2 border-emerald-300 shadow-sm">
                                            {{ strtoupper(substr($device->whatsapp_name ?: ($device->phone_number ?: 'WA'), 0, 2)) }}
                                        </div>
                                    @endif
                                    <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full"></span>
                                </div>
                                <div>
                                    <div class="text-[11px] text-emerald-800 font-bold uppercase tracking-wider">Akun WhatsApp Terhubung</div>
                                    <div class="text-base font-bold text-gray-900">{{ $device->whatsapp_name ?: 'Akun WhatsApp' }}</div>
                                    <div class="text-xs font-mono text-gray-600 mt-0.5">+{{ $device->phone_number }}</div>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-full text-xs font-bold shrink-0">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Sesi Terhubung
                            </span>
                        </div>
                    @endif
                    @if(auth()->user()->isSuperadmin())
                        <div class="p-4 bg-indigo-50 rounded-md border border-indigo-100 mb-6">
                            <label class="block text-sm font-bold text-indigo-900 flex items-center gap-1.5 mb-1">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                Alokasikan Penanggung Jawab Device
                            </label>
                            <select name="user_id" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm cursor-pointer">
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}" {{ old('user_id', $device->user_id) == $admin->id ? 'selected' : '' }}>
                                        {{ $admin->name }} ({{ $admin->email }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-indigo-700 mt-1">Gunakan fitur ini untuk memindahkan hak akses kelola obrolan device dari Admin saat ini ke Admin yang lain.</p>
                            @error('user_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Device / Instansi</label>
                        <input type="text" name="name" value="{{ old('name', $device->name) }}" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" required placeholder="Contoh: Silaq">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nomor WhatsApp Device</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number', $device->phone_number) }}" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono" required placeholder="Contoh: 6281803635400">
                        @error('phone_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">GOWA Session ID</label>
                        <input type="text" name="gowa_session_id" value="{{ old('gowa_session_id', $device->gowa_session_id) }}" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono" required placeholder="Contoh: silaq71">
                        @error('gowa_session_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Webhook URL (Meneruskan Data JSON)</label>
                        <input type="url" name="webhook_url" value="{{ old('webhook_url', $device->webhook_url) }}" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono" placeholder="https://api.serverlain.com/webhook">
                        <p class="text-[11px] text-gray-400 mt-1">Kosongkan jika tidak ingin meneruskan/forward data JSON GOWA ke aplikasi pihak ketiga lainnya.</p>
                        @error('webhook_url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">API Key SIMAWA (Untuk Pengiriman Pesan Eksternal)</label>
                        <div class="flex mt-1 shadow-sm rounded-md">
                            <input type="text" name="api_key" id="api_key" value="{{ old('api_key', $device->api_key) }}" class="flex-1 border-gray-300 rounded-l-md focus:border-indigo-500 focus:ring-indigo-500 text-sm font-mono bg-gray-50 text-indigo-700" readonly>
                            <button type="button" onclick="document.getElementById('api_key').value = Array.from(crypto.getRandomValues(new Uint8Array(16))).map(b => b.toString(16).padStart(2, '0')).join('')" class="bg-gray-200 border border-gray-300 border-l-0 rounded-r-md px-4 text-sm font-bold text-gray-700 hover:bg-gray-300 transition cursor-pointer">
                                ↺ Reset Key
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-1">Tempelkan kode ini ke dalam header <span class="font-mono text-gray-600 bg-gray-100 px-1 rounded">X-API-KEY</span> di aplikasi Anda. Jangan bagikan kunci ini kepada publik.</p>
                        @error('api_key') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mode Sistem Operasional</label>
                        <select name="is_livechat_enabled" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="1" {{ old('is_livechat_enabled', $device->is_livechat_enabled) == 1 ? 'selected' : '' }}>
                                Aktifkan Fungsi Live Chat & Forward Webhook
                            </option>
                            <option value="0" {{ old('is_livechat_enabled', $device->is_livechat_enabled) == 0 ? 'selected' : '' }}>
                                Hanya Forward Webhook Saja (Mute Live Chat SIMAWA)
                            </option>
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">Apabila memilih opsi "Hanya Forward", SIMAWA murni bekerja sebagai proxy tanpa mencatat pesan ke antrean ruang obrolan Live Chat.</p>
                        @error('is_livechat_enabled') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Kustomisasi Pesan Otomatis (Live Chat) -->
                    <div class="p-5 bg-gray-50 rounded-xl border border-gray-200 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                            <div>
                                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                    Kustomisasi Pesan Otomatis (Live Chat)
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">Edit pesan pembuka, pesan saat agen melayani, dan pesan penutup untuk device ini.</p>
                            </div>
                            <a href="{{ route('auto-replies.index', ['device_id' => $device->id]) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-1">
                                Mode Editor Penuh &rarr;
                            </a>
                        </div>

                        <!-- 1. Salam Pembuka -->
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-xs font-semibold text-gray-700">1. Salam Pembuka (Tiket Baru)</label>
                                <span class="text-[10px] text-gray-400 font-mono">{nama}, {nomor_tiket}, {device}</span>
                            </div>
                            <textarea name="greeting_message" rows="4" class="w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-sans" placeholder="Kosongkan jika ingin menggunakan pesan pembuka bawaan sistem...">{{ old('greeting_message', $device->greeting_message) }}</textarea>
                            @error('greeting_message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- 2. Pesan Diambil Agen -->
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-xs font-semibold text-gray-700">2. Pesan Diambil oleh Agen (Klaim Tiket)</label>
                                <span class="text-[10px] text-gray-400 font-mono">{agen}, {nama}, {device}</span>
                            </div>
                            <textarea name="claim_message" rows="2" class="w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-sans" placeholder="Kosongkan jika ingin menggunakan pesan klaim bawaan sistem...">{{ old('claim_message', $device->claim_message) }}</textarea>
                            @error('claim_message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- 3. Salam Penutup -->
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-xs font-semibold text-gray-700">3. Salam Penutup (Tiket Selesai)</label>
                                <span class="text-[10px] text-gray-400 font-mono">{nama}, {nomor_tiket}, {device}, {agen}</span>
                            </div>
                            <textarea name="closing_message" rows="4" class="w-full text-xs border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-sans" placeholder="Kosongkan jika ingin menggunakan pesan penutup bawaan sistem...">{{ old('closing_message', $device->closing_message) }}</textarea>
                            @error('closing_message') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                        <a href="{{ route('devices.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            Kembali
                        </a>
                        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 transition">
                            Simpan Perubahan
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>
