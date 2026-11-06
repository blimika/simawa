<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Kontak Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form method="POST" action="{{ route('phonebook.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="name" class="block font-medium text-sm text-gray-700">Nama Pelanggan</label>
                        <input id="name" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="text" name="name" value="{{ old('name') }}" required autofocus />
                        @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-6 border-b border-gray-200 pb-6">
                        <label for="number" class="block font-medium text-sm text-gray-700">Nomor WhatsApp</label>
                        <input id="number" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="text" name="number" value="{{ old('number') }}" placeholder="Contoh: 081234567890 atau 6281234567890" required />
                        <p class="text-xs text-gray-500 mt-1">Sistem akan otomatis memformat nomor menjadi standar awalan 62.</p>
                        @error('number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-6">
                        <label for="category" class="block font-medium text-sm text-gray-700">Kategori / Grup (Opsional)</label>

                        <input list="category-list" id="category" name="category" value="{{ old('category') }}" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Contoh: Tim Susenas, Ground Check 2026, dll." autocomplete="off" />

                        <datalist id="category-list">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>

                        <p class="text-xs text-gray-500 mt-1">Ketik nama grup baru, atau klik dua kali pada kotak untuk memilih grup yang sudah ada.</p>
                    </div>
                    <div class="flex items-center justify-end">
                        <a href="{{ route('phonebook.index') }}" class="underline text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md shadow transition duration-150 text-sm">
                            Simpan Kontak
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
