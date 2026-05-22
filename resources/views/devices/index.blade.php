<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Device') }}
            </h2>
            <a href="{{ route('devices.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                + Tambah Device
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Alert Success -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @elseif(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-3 px-6 border-b">Nama Device</th>
                                <th class="py-3 px-6 border-b">Nomor WA</th>
                                <th class="py-3 px-6 border-b">Session ID</th>
                                <th class="py-3 px-6 border-b">Status</th>
                                <th class="py-3 px-6 border-b text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm font-light">
                            @forelse($devices as $device)
                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                <td class="py-3 px-6 whitespace-nowrap font-medium">{{ $device->name }}</td>
                                <td class="py-3 px-6">{{ $device->phone_number }}</td>
                                <td class="py-3 px-6 font-mono text-xs bg-gray-100 rounded px-2">{{ $device->gowa_session_id }}</td>
                                <td class="py-3 px-6">
                                    @if($device->status == 'connected')
                                        <span class="bg-green-200 text-green-600 py-1 px-3 rounded-full text-xs">Terhubung</span>
                                    @else
                                        <span class="bg-yellow-200 text-yellow-700 py-1 px-3 rounded-full text-xs">Pending</span>
                                    @endif
                                </td>
                                <td class="py-3 px-6 text-center space-x-2 flex justify-center items-center">

                            <!-- 1. Tombol Cek Status (Selalu Muncul) -->
                            <form action="{{ route('devices.check_status', $device->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-xs bg-gray-200 hover:bg-gray-300 text-gray-700 py-1 px-2 rounded transition" title="Sinkronkan status dengan GOWA">
                                    Cek Status
                                </button>
                            </form>

                            @if($device->status === 'connected' || $device->status === 'logged')
                                <!-- Indikator jika sudah login -->
                                <span class="text-xs bg-green-100 text-green-700 py-1 px-2 rounded font-medium">Aktif</span>
                            @else
                                <!-- 2. Tombol Scan QR (Muncul jika pending / disconnected) -->
                                <a href="{{ route('devices.scan', $device->id) }}" class="text-xs bg-blue-600 hover:bg-blue-700 text-white font-medium py-1 px-3 rounded shadow transition">
                                    Scan QR
                                </a>

                                <!-- 3. Tombol Reconnect (HANYA muncul jika disconnected, bukan pending awal) -->
                                @if($device->status === 'disconnected')
                                    <form action="{{ route('devices.reconnect', $device->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs bg-yellow-100 hover:bg-yellow-200 text-yellow-800 py-1 px-2 rounded transition">
                                            Reconnect
                                        </button>
                                    </form>
                                @endif
                            @endif
                            <!-- Tambahkan Form Delete ini -->
                            <form action="{{ route('devices.destroy', $device->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus device ini? Semua data agen terkait akan dilepas.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded-md text-xs transition duration-150">
                                    Hapus
                                </button>
                            </form>
                        </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-500">Belum ada device yang didaftarkan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
