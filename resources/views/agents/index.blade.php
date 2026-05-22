<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Agen') }}
            </h2>
            <a href="{{ route('agents.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md text-sm transition duration-150">
                + Tambah Agen Baru
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
                                <th class="pb-3 px-4">Nama Agen</th>
                                <th class="pb-3 px-4">Username</th>
                                <th class="pb-3 px-4">Akses Device</th>
                                <th class="pb-3 px-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($agents as $agent)
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-3 px-4 font-medium">
                                        {{ $agent->name }}
                                        <div class="text-xs text-gray-500">{{ $agent->email }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-gray-700 font-mono text-sm">{{ $agent->username }}</td>
                                    <td class="py-3 px-4">
                                        @forelse($agent->handledDevices as $device)
                                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mr-1 mb-1 border border-blue-200">
                                                {{ $device->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-red-500 italic">Belum ada device</span>
                                        @endforelse
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex justify-center items-center gap-2">
                                            <a href="{{ route('agents.edit', $agent->id) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium bg-blue-50 py-1 px-3 rounded border border-blue-200 hover:bg-blue-100 transition">
                                                Ganti Password
                                            </a>
                                            <form action="{{ route('agents.destroy', $agent->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus agen ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium bg-red-50 py-1 px-3 rounded border border-red-200 hover:bg-red-100 transition">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-gray-500 italic">Belum ada data agen.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $agents->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
