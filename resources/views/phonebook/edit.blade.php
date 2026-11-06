<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('phonebook.index') }}" class="text-gray-500 hover:text-gray-700 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Kontak') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="w-full px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg shadow-sm">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6 bg-white border-b border-gray-100">

                    <form action="{{ route('phonebook.update', $phonebook->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nama Kontak <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="name" value="{{ old('name', $phonebook->name) }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition"
                                    placeholder="Masukkan nama kontak">
                            </div>

                            <div>
                                <label for="number" class="block text-sm font-medium text-gray-700">Nomor WhatsApp <span class="text-red-500">*</span></label>
                                <input type="text" name="number" id="number" value="{{ old('number', $phonebook->number) }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition"
                                    placeholder="Contoh: 081234567890">
                                <p class="mt-1 text-xs text-gray-500">Sistem akan otomatis mengubah awalan 0 menjadi format standar 62.</p>
                            </div>

                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700">Kategori</label>
                                <input list="category-list" name="category" id="category" value="{{ old('category', $phonebook->category) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition"
                                    placeholder="Ketik kategori baru atau pilih yang sudah ada">

                                <datalist id="category-list">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat }}">
                                    @endforeach
                                </datalist>
                                <p class="mt-1 text-xs text-gray-500">Kosongkan jika tidak ingin memasukkan ke kategori manapun.</p>
                            </div>
                        </div>

                        <div class="mt-8 flex items-center justify-end gap-3 border-t border-gray-50 pt-5">
                            <a href="{{ route('phonebook.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-medium text-gray-700 text-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                Batal
                            </a>
                            <button type="submit" class="px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-white text-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-sm transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
