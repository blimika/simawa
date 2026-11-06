<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                        {{ $campaign->name }}
                    </h2>
                    @if($campaign->status === 'completed')
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Selesai
                        </span>
                    @elseif($campaign->status === 'processing')
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                            <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
                            Sedang Mengirim
                        </span>
                    @elseif($campaign->status === 'scheduled')
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">
                            Terjadwal
                        </span>
                    @elseif($campaign->status === 'paused')
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            Dijeda
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                            {{ ucfirst($campaign->status) }}
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    Device: <strong>{{ $campaign->device->name ?? 'Device' }}</strong> | Jeda: <strong>{{ $campaign->delay_min }}-{{ $campaign->delay_max }} detik</strong> | Dibuat: {{ $campaign->created_at->format('d M Y H:i') }}
                </p>
            </div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="{{ route('broadcast.index') }}" class="text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 font-semibold py-2 px-3.5 rounded-lg text-xs transition shadow-sm">
                    &larr; Kembali
                </a>

                @if($campaign->status === 'processing')
                    <form action="{{ route('broadcast.pause', $campaign->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded-lg text-xs transition shadow-sm">
                            ⏸ Jeda Pengiriman
                        </button>
                    </form>
                @elseif($campaign->status === 'paused' || ($campaign->status === 'pending' && $campaign->pending_count > 0))
                    <form action="{{ route('broadcast.resume', $campaign->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg text-xs transition shadow-sm">
                            ▶ Lanjutkan Pengiriman
                        </button>
                    </form>
                @endif

                @if($campaign->failed_count > 0)
                    <form action="{{ route('broadcast.retry_failed', $campaign->id) }}" method="POST" class="inline" onsubmit="return confirm('Kirim ulang semua pesan yang gagal?');">
                        @csrf
                        <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold py-2 px-4 rounded-lg text-xs transition shadow-sm">
                            ↺ Kirim Ulang Gagal ({{ $campaign->failed_count }})
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen" x-data="{
        campaignStatus: '{{ $campaign->status }}',
        totalCount: {{ $campaign->total_recipients }},
        sentCount: {{ $campaign->sent_count }},
        failedCount: {{ $campaign->failed_count }},
        pendingCount: {{ $campaign->pending_count }},
        progressPct: {{ $campaign->progress_percentage }},
        isCompleted: {{ $campaign->isCompleted() ? 'true' : 'false' }},
        timer: null,
        pollStatus() {
            if (this.isCompleted || this.campaignStatus === 'paused') return;
            fetch('{{ route('broadcast.status_json', $campaign->id) }}')
                .then(res => res.json())
                .then(data => {
                    this.campaignStatus = data.status;
                    this.totalCount = data.total;
                    this.sentCount = data.sent;
                    this.failedCount = data.failed;
                    this.pendingCount = data.pending;
                    this.progressPct = data.progress;
                    if (data.is_completed) {
                        this.isCompleted = true;
                        clearInterval(this.timer);
                        setTimeout(() => window.location.reload(), 1500);
                    }
                })
                .catch(() => {});
        },
        init() {
            if (!this.isCompleted && this.campaignStatus !== 'paused') {
                this.timer = setInterval(() => this.pollStatus(), 3000);
            }
        }
    }">
        <div class="w-full px-4 sm:px-6 lg:px-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-5 bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3.5 rounded-xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 bg-red-50 border border-red-300 text-red-800 px-4 py-3.5 rounded-xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">&times;</button>
                </div>
            @endif

            {{-- Kartu Progres & Statistik --}}
            <div class="mb-6 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-3">
                    <div>
                        <h3 class="font-bold text-gray-800 text-base">Progres Pengiriman Broadcast</h3>
                        <p class="text-xs text-gray-400">Pembaruan otomatis realtime setiap 3 detik</p>
                    </div>
                    <div class="text-2xl font-black text-indigo-600" x-text="progressPct + '%'">
                        {{ $campaign->progress_percentage }}%
                    </div>
                </div>

                {{-- Bar Progres --}}
                <div class="w-full bg-gray-100 rounded-full h-3.5 overflow-hidden mb-6 p-0.5 border border-gray-200">
                    <div class="h-2.5 rounded-full bg-gradient-to-r from-indigo-500 to-emerald-500 transition-all duration-500" :style="'width: ' + progressPct + '%'"></div>
                </div>

                {{-- Grid Statistik 4 Kolom --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-gray-50 border border-gray-200/80 p-4 rounded-xl">
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Penerima</div>
                        <div class="text-2xl font-black text-gray-900 mt-1" x-text="totalCount">
                            {{ number_format($campaign->total_recipients) }}
                        </div>
                    </div>

                    <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-xl">
                        <div class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Berhasil Terkirim</div>
                        <div class="text-2xl font-black text-emerald-600 mt-1" x-text="sentCount">
                            {{ number_format($campaign->sent_count) }}
                        </div>
                    </div>

                    <div class="bg-rose-50 border border-rose-200 p-4 rounded-xl">
                        <div class="text-xs font-bold text-rose-800 uppercase tracking-wider">Gagal Terkirim</div>
                        <div class="text-2xl font-black text-rose-600 mt-1" x-text="failedCount">
                            {{ number_format($campaign->failed_count) }}
                        </div>
                    </div>

                    <div class="bg-indigo-50 border border-indigo-200 p-4 rounded-xl">
                        <div class="text-xs font-bold text-indigo-800 uppercase tracking-wider">Sisa Antrean</div>
                        <div class="text-2xl font-black text-indigo-600 mt-1" x-text="pendingCount">
                            {{ number_format($campaign->pending_count) }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pesan Broadcast Yang Dikirim --}}
            <div class="mb-6 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Template Pesan Broadcast:</h4>
                <div class="bg-gray-50 p-4 rounded-xl text-sm text-gray-800 font-sans whitespace-pre-wrap border border-gray-200/80 leading-relaxed">{{ $campaign->message }}</div>
            </div>

            {{-- Tabel Laporan Rincian Status per Kontak --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100">
                <div class="p-5 border-b border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h3 class="font-bold text-gray-800 text-base">Laporan Status Penerima</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Menampilkan status pengiriman dan pesan error server untuk setiap kontak</p>
                    </div>

                    {{-- Filter & Search Form --}}
                    <form action="{{ route('broadcast.show', $campaign->id) }}" method="GET" class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                        <select name="status" onchange="this.form.submit()" class="text-xs border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 py-1.5 px-3">
                            <option value="">-- Semua Status --</option>
                            <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Terkirim</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Gagal</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Antrean</option>
                            <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Sedang Dikirim</option>
                        </select>

                        <div class="relative w-full sm:w-60">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, nomor, error..." class="w-full text-xs border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 pl-8 py-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>

                        <button type="submit" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg font-semibold transition">
                            Cari
                        </button>

                        @if(request('status') || request('search'))
                            <a href="{{ route('broadcast.show', $campaign->id) }}" class="text-xs text-rose-600 hover:text-rose-800 font-semibold px-2">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 text-gray-600 uppercase text-xs font-semibold tracking-wider border-b border-gray-200">
                                <th class="py-3 px-6">Nama Penerima</th>
                                <th class="py-3 px-6">Nomor WhatsApp</th>
                                <th class="py-3 px-6">Status Pengiriman</th>
                                <th class="py-3 px-6">Waktu Kirim</th>
                                <th class="py-3 px-6">Keterangan / Error Server</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm divide-y divide-gray-100">
                            @forelse($recipients as $item)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="py-3.5 px-6 whitespace-nowrap">
                                        <div class="font-bold text-gray-900 text-sm">{{ $item->name }}</div>
                                    </td>

                                    <td class="py-3.5 px-6 whitespace-nowrap text-xs font-mono font-medium text-gray-800">
                                        +{{ $item->number }}
                                    </td>

                                    <td class="py-3.5 px-6 whitespace-nowrap">
                                        @if($item->status === 'sent')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                Terkirim
                                            </span>
                                        @elseif($item->status === 'failed')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                                                <svg class="w-3.5 h-3.5 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                                Gagal
                                            </span>
                                        @elseif($item->status === 'processing')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                                <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
                                                Sedang Mengirim
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                Menunggu Antrean
                                            </span>
                                        @endif
                                    </td>

                                    <td class="py-3.5 px-6 whitespace-nowrap text-xs text-gray-500">
                                        {{ $item->sent_at ? $item->sent_at->format('d M Y H:i:s') : '-' }}
                                    </td>

                                    <td class="py-3.5 px-6 text-xs">
                                        @if($item->error_message)
                                            <div class="p-2 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg max-w-md font-mono text-[11px] leading-relaxed">
                                                <strong>Error:</strong> {{ $item->error_message }}
                                            </div>
                                        @elseif($item->status === 'sent')
                                            <span class="text-emerald-600 font-semibold text-xs">Sukses diterima server WhatsApp</span>
                                        @else
                                            <span class="text-gray-400 italic text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-gray-400 bg-gray-50">
                                        Tidak ada data penerima yang cocok dengan filter yang dipilih.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($recipients, 'links'))
                    <div class="p-4 border-t border-gray-100 bg-white">
                        {{ $recipients->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
