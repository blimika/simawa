<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Device WhatsApp') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">

                <form action="{{ route('devices.update', $device->id) }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    @method('PUT') <div>
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
