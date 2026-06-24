<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Webhook Log History') }}
            </h2>
            <form action="{{ route('webhook-logs.clear') }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus semua log? Data tidak dapat dikembalikan.')">
                @csrf
                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium transition shadow-sm">
                    🗑️ Bersihkan Log
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Device ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pengirim</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pesan Singkat</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payload Detail</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($logs as $log)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $log->created_at->format('d M Y H:i:s') }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-indigo-600 text-center">
                                            {{ $log->device_id }}
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-mono text-gray-700">
                                            {{ $log->sender_number ?? '-' }}
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-600 max-w-xs truncate" title="{{ $log->message }}">
                                            @if($log->message)
                                                {{ Str::limit($log->message, 40) }}
                                            @else
                                                <span class="text-gray-400 italic">NULL / Media</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-900">
                                            <details class="group">
                                                <summary class="cursor-pointer text-blue-500 hover:text-blue-700 font-medium list-none flex items-center text-xs">
                                                    <svg class="w-4 h-4 mr-1 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                                    Lihat JSON
                                                </summary>
                                                <div class="mt-2 p-3 bg-gray-900 rounded-md overflow-x-auto w-full max-w-lg">
                                                    @php
                                                        // Memastikan payload ditampilkan rapi meskipun formatnya string dari database
                                                        $payloadData = is_string($log->payload) ? json_decode($log->payload, true) : $log->payload;
                                                        $prettyJson = json_encode($payloadData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                                                    @endphp
                                                    <pre class="text-[10px] text-green-400 font-mono whitespace-pre-wrap">{{ $prettyJson ?: $log->payload }}</pre>
                                                </div>
                                            </details>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">Belum ada data Webhook yang masuk.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
