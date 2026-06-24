<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Buku Telepon (Phonebook)') }}
            </h2>
            <a href="{{ route('phonebook.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition shadow-sm">
                + Tambah Kontak
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="mb-6 bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h3 class="font-bold text-gray-800 text-lg">Import Kontak Massal</h3>
                    <p class="text-sm text-gray-500 mt-1 mb-2">Upload file Excel yang disimpan dengan format <strong>.CSV</strong> (Comma delimited).</p>

                    <a href="{{ route('phonebook.template') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-md transition w-max">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download Template CSV
                    </a>
                </div>

                <form action="{{ route('phonebook.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                    @csrf
                    <input type="file" name="file" accept=".csv" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition">
                    <button type="submit" class="w-full sm:w-auto shrink-0 whitespace-nowrap bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-full text-sm font-bold transition shadow-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Import File
                    </button>
                </form>
            </div>

            <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                <form id="bulkDeleteForm" action="{{ route('phonebook.bulk-delete') }}" method="POST">
                    @csrf

                    <div class="p-4 border-b border-gray-100 bg-white flex justify-between items-center">
                        <h3 class="font-semibold text-gray-700">Daftar Kontak</h3>

                        <div id="bulkDeleteBtnContainer" class="hidden">
                            <button type="submit" onclick="return confirm('Peringatan: Anda akan menghapus semua kontak terpilih secara permanen. Lanjutkan?')" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm font-bold transition shadow-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                Hapus (<span id="selectedCount">0</span>) Terpilih
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 w-10 text-center">
                                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 cursor-pointer">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Kontak</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nomor WhatsApp</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse ($contacts as $contact)
                                    <tr class="hover:bg-gray-50/70 transition">
                                        <td class="px-6 py-4 text-center">
                                            <input type="checkbox" name="ids[]" value="{{ $contact->id }}" class="item-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 cursor-pointer">
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs uppercase mr-3">
                                                    {{ substr($contact->name, 0, 2) }}
                                                </div>
                                                <span class="text-sm font-semibold text-gray-900">{{ $contact->name }}</span>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">
                                            {{ $contact->number }}
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($contact->category)
                                                <span class="px-2.5 py-1 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-full">
                                                    {{ $contact->category }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 italic text-xs">-</span>
                                            @endif
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end gap-3">
                                                <a href="{{ route('phonebook.edit', $contact->id) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">Edit</a>

                                                <button type="submit" formaction="{{ route('phonebook.destroy', $contact->id) }}" formmethod="POST" onclick="return confirm('Hapus kontak ini?')" class="text-red-600 hover:text-red-900 font-semibold">
                                                    @method('DELETE')
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400 italic bg-gray-50">
                                            Buku telepon Anda masih kosong. Silakan tambah manual atau lakukan import CSV.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>

                @if(method_exists($contacts, 'links'))
                    <div class="p-4 border-t border-gray-100 bg-white">
                        {{ $contacts->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <script>
        (function() {
            // Fungsi sentral untuk kalkulasi status checkbox
            function checkBulkDeleteStatus() {
                const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
                const totalCount = document.querySelectorAll('.item-checkbox').length;
                const btnContainer = document.getElementById('bulkDeleteBtnContainer');
                const selectedCountText = document.getElementById('selectedCount');
                const masterCheckbox = document.getElementById('selectAll');

                if (selectedCountText) {
                    selectedCountText.textContent = checkedCount;
                }

                if (checkedCount > 0) {
                    if (btnContainer) btnContainer.classList.remove('hidden');
                } else {
                    if (btnContainer) btnContainer.classList.add('hidden');
                }

                if (masterCheckbox && totalCount > 0) {
                    masterCheckbox.checked = (checkedCount === totalCount);
                }
            }

            // CCTV Global: Menggunakan Event Delegation (Metode paling stabil)
            document.addEventListener('change', function(e) {
                // 1. Jika Klik Master Checkbox (Pilih Semua)
                if (e.target && e.target.id === 'selectAll') {
                    const isChecked = e.target.checked;
                    const itemCheckboxes = document.querySelectorAll('.item-checkbox');

                    itemCheckboxes.forEach(function(checkbox) {
                        checkbox.checked = isChecked;
                    });

                    checkBulkDeleteStatus();
                }
                // 2. Jika Klik Checkbox Satuan
                else if (e.target && e.target.classList.contains('item-checkbox')) {
                    checkBulkDeleteStatus();
                }
            });

            // Antisipasi jika browser melakukan autofill/cache centang saat back page
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', checkBulkDeleteStatus);
            } else {
                checkBulkDeleteStatus();
            }
        })();
    </script>
</x-app-layout>
