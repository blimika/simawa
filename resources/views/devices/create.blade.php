<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Device Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form action="{{ route('devices.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Nama Device -->
                        <div>
                            <x-input-label for="name" value="Nama Device / Layanan" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" placeholder="Contoh: CS Silaq" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <!-- Nomor WA -->
                        <div>
                            <x-input-label for="phone_number" value="Nomor WhatsApp" />
                            <x-text-input id="phone_number" name="phone_number" type="text" class="mt-1 block w-full" placeholder="Contoh: 6281234567890" required />
                            <p class="text-xs text-gray-500 mt-1">Gunakan kode negara (62) tanpa tanda plus (+).</p>
                            <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
                        </div>

                        <!-- Session ID -->
                        <div>
                            <x-input-label for="gowa_session_id" value="GOWA Session ID" />
                            <x-text-input id="gowa_session_id" name="gowa_session_id" type="text" class="mt-1 block w-full bg-gray-50" placeholder="Contoh: silaq_utama_1" required />
                            <p class="text-xs text-gray-500 mt-1">ID unik untuk dikenali oleh API GOWA (tanpa spasi).</p>
                            <x-input-error class="mt-2" :messages="$errors->get('gowa_session_id')" />
                        </div>

                        <!-- Webhook URL (Opsional) -->
                        <div>
                            <x-input-label for="webhook_url" value="Webhook URL (Opsional)" />
                            <x-text-input id="webhook_url" name="webhook_url" type="url" class="mt-1 block w-full" placeholder="https://aplikasi-lain.com/api/webhook" />
                            <p class="text-xs text-gray-500 mt-1">Kosongkan jika device ini hanya dipakai untuk Live Chat SIMAWA.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('webhook_url')" />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Mode Layanan Device</label>
                            <select name="is_livechat_enabled" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="1" {{ (isset($device) && $device->is_livechat_enabled == 1) || !isset($device) ? 'selected' : '' }}>
                                    Aktifkan Live Chat & Webhook
                                </option>
                                <option value="0" {{ isset($device) && $device->is_livechat_enabled == 0 ? 'selected' : '' }}>
                                    Hanya Forward Webhook Saja
                                </option>
                            </select>
                            <p class="text-[10px] text-gray-500 mt-1">Jika diset "Hanya Forward", pesan tidak akan masuk ke menu Live Chat SIMAWA.</p>
                        </div>
                        <div class="flex items-center gap-4 mt-6">
                            <x-primary-button>Simpan Device</x-primary-button>
                            <a href="{{ route('devices.index') }}" class="text-gray-600 hover:text-gray-900 text-sm">Batal</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
