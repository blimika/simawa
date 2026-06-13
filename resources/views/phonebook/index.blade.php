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
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium flex justify-center gap-2">
                                        <button onclick="openEditModal({{ $contact->id }}, '{{ addslashes($contact->name) }}', '{{ $contact->number }}', '{{ addslashes($contact->category) }}')" class="bg-amber-100 text-amber-700 hover:bg-amber-200 px-3 py-1.5 rounded transition flex items-center gap-1 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            Edit
                                        </button>

                                        <form action="{{ route('phonebook.destroy', $contact->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus kontak {{ $contact->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-rose-100 text-rose-700 hover:bg-rose-200 px-3 py-1.5 rounded transition flex items-center gap-1 shadow-sm">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                Hapus
                                            </button>
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
    <div id="editContactModal" class="fixed inset-0 z-[70] hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeEditModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-visible shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                <form action="" method="POST" id="formEditContact">
                    @csrf
                    @method('PUT') <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 rounded-t-lg">
                        <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4 border-b pb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Edit Kontak
                        </h3>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                            <input type="text" name="name" id="editModalName" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm" required autofocus>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Nomor WhatsApp</label>
                            <input type="text" name="number" id="editModalNumber" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm font-mono" required>
                        </div>

                        <div class="mb-2">
                            <label class="block text-sm font-medium text-gray-700">Grup / Kategori</label>
                            <input type="text" name="category" id="editModalCategory" class="mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm" placeholder="Contoh: VIP, Rekan Kerja, Dinas">
                            <p class="text-[10px] text-gray-500 mt-1">Boleh dikosongkan jika tidak ada grup.</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-lg">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-amber-600 text-base font-medium text-white hover:bg-amber-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan Perubahan
                        </button>
                        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeEditModal()">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        // --- FUNGSI MODAL EDIT KONTAK ---
        function openEditModal(id, name, number, category) {
            document.getElementById('editContactModal').classList.remove('hidden');

            // Isi form dengan data saat ini
            document.getElementById('editModalName').value = name;
            document.getElementById('editModalNumber').value = number;

            // Jika kategori kosong/null, jadikan string kosong
            document.getElementById('editModalCategory').value = category !== 'null' && category ? category : '';

            // Ubah action URL form
            document.getElementById('formEditContact').action = '/phonebook/' + id;
        }

        function closeEditModal() {
            document.getElementById('editContactModal').classList.add('hidden');
        }
    </script>
</x-app-layout>
