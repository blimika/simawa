<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Buku Telepon') }}
            </h2>
            <a href="{{ route('phonebook.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md text-sm transition duration-150">
                + Tambah Kontak
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="w-full whitespace-nowrap">
                        <thead>
                            <tr class="text-left font-bold text-gray-500 border-b-2 border-gray-200">
                                <th class="pb-3 px-4">Nama Pelanggan</th>
                                <th class="pb-3 px-4">Nomor WhatsApp</th>
                                <th class="pb-3 px-4">Grup / Kategori</th>
                                @if(auth()->user()->isSuperadmin())
                                    <th class="pb-3 px-4">Milik Admin</th>
                                @endif
                                <th class="pb-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($contacts as $contact)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-4 font-medium text-gray-800">{{ $contact->name }}</td>
                                    <td class="py-3 px-4 text-green-600 font-mono">{{ $contact->number }}</td>
                                    <td class="py-3 px-4">
                                        @if($contact->category)
                                            <span class="bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded">{{ $contact->category }}</span>
                                        @else
                                            <span class="text-gray-400 text-xs italic">-</span>
                                        @endif
                                    </td>
                                    @if(auth()->user()->isSuperadmin())
                                        <td class="py-3 px-4 text-gray-600 text-sm">{{ $contact->owner->name ?? 'Unknown' }}</td>
                                    @endif
                                    <td class="py-3 px-4 text-center">
                                        <form action="{{ route('phonebook.destroy', $contact->id) }}" method="POST" onsubmit="return confirm('Hapus kontak ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium bg-red-50 py-1 px-3 rounded border border-red-200 hover:bg-red-100 transition">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->isSuperadmin() ? '4' : '3' }}" class="py-6 text-center text-gray-500 italic">Belum ada kontak yang disimpan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $contacts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
