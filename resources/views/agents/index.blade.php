<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Agen') }}
            </h2>
            <!-- Tombol Tambah Agen Manual -->
            <a href="{{ route('agents.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition shadow-sm">
                + Tambah Agen Manual
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="w-full px-4 sm:px-6 lg:px-8">

            <!-- Notifikasi Sukses / Error -->
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

            <!-- ========================================== -->
            <!-- BLOK IMPORT EXCEL / CSV                    -->
            <!-- ========================================== -->
            <div class="mb-6 bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h3 class="font-bold text-gray-800 text-lg">Import Agen Massal</h3>
                    <p class="text-sm text-gray-500 mt-1 mb-2">Upload file format <strong>.CSV</strong>. Kolom: Nama, Username, Email, Password.</p>

                    <!-- Tombol Download Template CSV -->
                    <a href="{{ route('agents.template') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-md transition w-max">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download Template CSV
                    </a>
                </div>

                <form action="{{ route('agents.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                    @csrf
                    <input type="file" name="file" accept=".csv" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition">
                    <button type="submit" class="w-full sm:w-auto shrink-0 whitespace-nowrap bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-full text-sm font-bold transition shadow-sm flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Import File
                    </button>
                </form>
            </div>

            <!-- ========================================== -->
            <!-- BLOK TABEL & DAFTAR AGEN                   -->
            <!-- ========================================== -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">

                <div class="p-4 border-b border-gray-100 bg-white flex justify-between items-center">
                    <h3 class="font-semibold text-gray-700">Daftar Agen CS</h3>

                    <!-- Tombol Hapus Massal (Dipicu via JS untuk mencegah nested form) -->
                    <div id="bulkDeleteBtnContainer" class="hidden">
                        <button type="button" onclick="executeBulkDelete()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm font-bold transition shadow-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Hapus (<span id="selectedCount">0</span>) Terpilih
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <!-- Checkbox Induk -->
                                <th class="px-6 py-3 w-10 text-center">
                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 cursor-pointer">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Agen</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kontak & Akses</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse ($agents as $agent)
                                <tr class="hover:bg-gray-50/70 transition">
                                    <!-- Checkbox Item -->
                                    <td class="px-6 py-4 text-center">
                                        <input type="checkbox" name="ids[]" value="{{ $agent->id }}" class="item-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 cursor-pointer">
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs uppercase mr-3">
                                                {{ substr($agent->name, 0, 2) }}
                                            </div>
                                            <span class="text-sm font-semibold text-gray-900">{{ $agent->name }}</span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $agent->email }}</div>
                                        <div class="text-xs text-indigo-500 font-mono mt-0.5">Username: {{ $agent->username }}</div>
                                        <div class="mt-2 flex flex-wrap gap-1 items-center">
                                            @if($agent->handledDevices->isNotEmpty())
                                                @foreach($agent->handledDevices as $dev)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-indigo-50 text-indigo-700 border border-indigo-200" title="Device: {{ $dev->name }} (Status: {{ $dev->status }})">
                                                        <svg class="w-3 h-3 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                                        {{ $dev->name }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200" title="Belum memiliki akses ke device WhatsApp">
                                                    <svg class="w-3 h-3 mr-1 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                    Belum ada akses device
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end items-center gap-4">
                                            <!-- Tombol Edit Satuan -->
                                            <a href="{{ route('agents.edit', $agent->id) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                Edit
                                            </a>

                                            <!-- Tombol Hapus Satuan -->
                                            <form action="{{ route('agents.destroy', $agent->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus agen {{ $agent->name }} secara permanen?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-semibold flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-400 italic bg-gray-50">
                                        Belum ada agen yang terdaftar. Silakan tambah manual atau import via CSV.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if(method_exists($agents, 'links'))
                    <div class="p-4 border-t border-gray-100 bg-white">
                        {{ $agents->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- ======================================================== -->
    <!-- SCRIPT JAVASCRIPT UNTUK CHECKBOX DAN HAPUS MASSAL        -->
    <!-- ======================================================== -->
    <script>
        (function() {
            // Fungsi kalkulasi status UI checkbox
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

            // Event Delegation untuk Deteksi Centang
            document.addEventListener('change', function(e) {
                if (e.target && e.target.id === 'selectAll') {
                    const isChecked = e.target.checked;
                    document.querySelectorAll('.item-checkbox').forEach(function(checkbox) {
                        checkbox.checked = isChecked;
                    });
                    checkBulkDeleteStatus();
                }
                else if (e.target && e.target.classList.contains('item-checkbox')) {
                    checkBulkDeleteStatus();
                }
            });

            // Inisialisasi awal saat halaman dimuat
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', checkBulkDeleteStatus);
            } else {
                checkBulkDeleteStatus();
            }

            // FUNGSI EKSEKUSI HAPUS MASSAL VIA JAVASCRIPT
            window.executeBulkDelete = function() {
                if(confirm('Peringatan: Anda akan menghapus semua agen yang dicentang secara permanen. Lanjutkan?')) {
                    const checkedCheckboxes = document.querySelectorAll('.item-checkbox:checked');

                    // Buat form virtual
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("agents.bulk-delete") }}';

                    // Tambahkan CSRF Token
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);

                    // Tambahkan ID yang dicentang ke dalam form
                    checkedCheckboxes.forEach(function(checkbox) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = checkbox.value;
                        form.appendChild(input);
                    });

                    // Kirim form ke Controller
                    document.body.appendChild(form);
                    form.submit();
                }
            };
        })();
    </script>
</x-app-layout>
