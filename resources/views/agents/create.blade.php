<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Agen Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form method="POST" action="{{ route('agents.store') }}">
                    @csrf

                    <!-- Nama -->
                    <div class="mb-4">
                        <label for="name" class="block font-medium text-sm text-gray-700">Nama Lengkap</label>
                        <input id="name" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="text" name="name" value="{{ old('name') }}" required autofocus />
                        @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <!-- Username -->
                    <div class="mb-4">
                        <label for="username" class="block font-medium text-sm text-gray-700">Username Login</label>
                        <input id="username" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="text" name="username" value="{{ old('username') }}" required />
                        <p class="text-xs text-gray-500 mt-1">Gunakan huruf tanpa spasi (misal: agensatu).</p>
                        @error('username') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="block font-medium text-sm text-gray-700">Email Login</label>
                        <input id="email" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="email" name="email" value="{{ old('email') }}" required />
                        @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="block font-medium text-sm text-gray-700">Password</label>
                        <input id="password" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="password" name="password" required />
                        @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-6">
                        <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Konfirmasi Password</label>
                        <input id="password_confirmation" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="password" name="password_confirmation" required />
                    </div>

                    <!-- Pilihan Device -->
                    <div class="mb-6 border-t border-gray-200 pt-4">
                        <label class="block font-bold text-sm text-gray-800 mb-2">Pilih Akses Device (Opsional)</label>
                        <p class="text-xs text-gray-500 mb-3">Tentukan device WhatsApp mana saja yang bisa digunakan oleh agen ini untuk membalas pesan.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @forelse($devices as $device)
                                <label class="inline-flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="checkbox" name="devices[]" value="{{ $device->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700 font-medium">{{ $device->name }}</span>
                                </label>
                            @empty
                                <div class="col-span-2 text-sm text-red-500 italic">Belum ada device yang berstatus Terhubung. Pastikan ada device yang terkoneksi terlebih dahulu.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <a href="{{ route('agents.index') }}" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4">
                            Batal
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow transition duration-150 text-sm">
                            Simpan Agen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
