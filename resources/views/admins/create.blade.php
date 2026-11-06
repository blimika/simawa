<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Admin Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form method="POST" action="{{ route('admins.store') }}">
                    @csrf

                    <!-- Nama -->
                    <div class="mb-4">
                        <label for="name" class="block font-medium text-sm text-gray-700">Nama Admin</label>
                        <input id="name" class="block mt-1 w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm" type="text" name="name" value="{{ old('name') }}" required autofocus />
                        @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Username -->
                    <div class="mb-4">
                        <label for="username" class="block font-medium text-sm text-gray-700">Username Login</label>
                        <input id="username" class="block mt-1 w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm" type="text" name="username" value="{{ old('username') }}" required />
                        <p class="text-xs text-gray-500 mt-1">Gunakan huruf tanpa spasi (misal: adminkota).</p>
                        @error('username') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="block font-medium text-sm text-gray-700">Email Login</label>
                        <input id="email" class="block mt-1 w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm" type="email" name="email" value="{{ old('email') }}" required />
                        @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="block font-medium text-sm text-gray-700">Password</label>
                        <input id="password" class="block mt-1 w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm" type="password" name="password" required />
                        @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-6 border-b border-gray-200 pb-6">
                        <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Konfirmasi Password</label>
                        <input id="password_confirmation" class="block mt-1 w-full border-gray-300 focus:border-purple-500 focus:ring-purple-500 rounded-md shadow-sm" type="password" name="password_confirmation" required />
                    </div>

                    <div class="flex items-center justify-end">
                        <a href="{{ route('admins.index') }}" class="underline text-sm text-gray-600 hover:text-gray-900 mr-4">
                            Batal
                        </a>
                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-md shadow transition duration-150 text-sm">
                            Simpan Admin
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
