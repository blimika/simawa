<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Kirim Broadcast (Pesan Massal)') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Kirim pengumuman atau promosi massal dengan proteksi jeda anti-blokir dan scheduler di latar belakang.
                </p>
            </div>
            <a href="{{ route('broadcast.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-lg text-sm transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                + Buat Broadcast Baru
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
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

            {{-- Kartu Info Fitur Anti-Blokir & Scheduler --}}
            <div class="mb-6 bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">Sistem Pengiriman Otomatis & Terjadwal</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Pesan massal dikirim secara asinkron di latar belakang menggunakan jeda acak manusia sehingga nomor Anda aman dari pemblokiran WhatsApp.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('broadcast.create') }}" class="text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3.5 py-2 rounded-lg transition">
                        Buat Pesan Massal Baru &rarr;
                    </a>
                </div>
            </div>

            {{-- Tabel Daftar Broadcast --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 text-gray-600 uppercase text-xs font-semibold tracking-wider border-b border-gray-200">
                                <th class="py-3.5 px-6">Nama & Info Broadcast</th>
                                <th class="py-3.5 px-6">Device Pengirim</th>
                                <th class="py-3.5 px-6">Target Kontak</th>
                                <th class="py-3.5 px-6">Jadwal / Waktu</th>
                                <th class="py-3.5 px-6">Progres Pengiriman</th>
                                <th class="py-3.5 px-6">Status</th>
                                <th class="py-3.5 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm divide-y divide-gray-100">
                            @forelse($campaigns as $campaign)
                                <tr class="hover:bg-gray-50/80 transition">
                                    {{-- Nama & Tanggal --}}
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <div class="font-bold text-gray-900 text-sm flex items-center gap-2">
                                            <span>{{ $campaign->name }}</span>
                                        </div>
                                        <div class="text-[11px] text-gray-400 mt-0.5">
                                            Dibuat: {{ $campaign->created_at->format('d M Y H:i') }}
                                        </div>
                                    </td>

                                    {{-- Device Pengirim --}}
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        @if($campaign->device)
                                            <div class="flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full {{ $campaign->device->status === 'connected' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                                <span class="font-semibold text-gray-800 text-xs">{{ $campaign->device->name }}</span>
                                            </div>
                                            <div class="text-[11px] font-mono text-gray-400 mt-0.5">
                                                +{{ $campaign->device->phone_number }}
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Device Dihapus</span>
                                        @endif
                                    </td>

                                    {{-- Target Kontak --}}
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        @if($campaign->category === 'all')
                                            <span class="px-2.5 py-1 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-full">
                                                Semua Kontak
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-full">
                                                Grup: {{ $campaign->category }}
                                            </span>
                                        @endif
                                        <div class="text-[11px] text-gray-500 mt-1">
                                            Total: <strong>{{ number_format($campaign->total_recipients) }}</strong> kontak
                                        </div>
                                    </td>

                                    {{-- Jadwal / Waktu --}}
                                    <td class="py-4 px-6 whitespace-nowrap text-xs text-gray-700">
                                        @if($campaign->scheduled_at && $campaign->scheduled_at > now() && $campaign->status === 'scheduled')
                                            <div class="flex items-center gap-1.5 text-purple-700 font-semibold">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                Terjadwal:
                                            </div>
                                            <div class="font-mono mt-0.5">{{ $campaign->scheduled_at->format('d M Y H:i') }}</div>
                                        @else
                                            <div class="text-gray-500">Kirim Langsung</div>
                                            <div class="text-[11px] text-gray-400">Jeda: {{ $campaign->delay_min }}-{{ $campaign->delay_max }}s</div>
                                        @endif
                                    </td>

                                    {{-- Progres Pengiriman --}}
                                    <td class="py-4 px-6 whitespace-nowrap" style="min-width: 180px;">
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="font-semibold text-gray-700">
                                                {{ $campaign->sent_count + $campaign->failed_count }} / {{ $campaign->total_recipients }}
                                            </span>
                                            <span class="font-bold {{ $campaign->progress_percentage === 100 ? 'text-emerald-600' : 'text-indigo-600' }}">
                                                {{ $campaign->progress_percentage }}%
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                            <div class="h-2 rounded-full transition-all duration-500 {{ $campaign->failed_count > 0 ? 'bg-amber-500' : 'bg-emerald-500' }}" style="width: {{ $campaign->progress_percentage }}%"></div>
                                        </div>
                                        <div class="flex items-center gap-3 text-[11px] mt-1.5">
                                            <span class="text-emerald-600 font-medium">✓ {{ $campaign->sent_count }} Terkirim</span>
                                            @if($campaign->failed_count > 0)
                                                <span class="text-rose-600 font-medium">✕ {{ $campaign->failed_count }} Gagal</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        @if($campaign->status === 'completed')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                Selesai
                                            </span>
                                        @elseif($campaign->status === 'processing')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                                <span class="w-2 h-2 rounded-full bg-blue-500 animate-ping"></span>
                                                Sedang Mengirim
                                            </span>
                                        @elseif($campaign->status === 'scheduled')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 border border-purple-200">
                                                <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                Terjadwal
                                            </span>
                                        @elseif($campaign->status === 'paused')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                                <svg class="w-3.5 h-3.5 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                                Dijeda
                                            </span>
                                        @elseif($campaign->status === 'failed')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                                                Gagal
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">
                                                Antrean
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="py-4 px-6 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            {{-- Detail & Laporan --}}
                                            <a href="{{ route('broadcast.show', $campaign->id) }}" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition shadow-sm" title="Lihat laporan status pengiriman">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                Laporan
                                            </a>

                                            {{-- Jeda / Lanjutkan --}}
                                            @if($campaign->status === 'processing')
                                                <form action="{{ route('broadcast.pause', $campaign->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-xs font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 px-2.5 py-1.5 rounded-lg transition" title="Jeda pengiriman">
                                                        Jeda
                                                    </button>
                                                </form>
                                            @elseif($campaign->status === 'paused' || ($campaign->status === 'pending' && $campaign->pending_count > 0))
                                                <form action="{{ route('broadcast.resume', $campaign->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-2.5 py-1.5 rounded-lg transition" title="Lanjutkan pengiriman">
                                                        Lanjutkan
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- Hapus --}}
                                            <form action="{{ route('broadcast.destroy', $campaign->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus riwayat broadcast ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800 p-1.5 rounded transition" title="Hapus broadcast">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-gray-400 bg-gray-50">
                                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                        <p class="font-medium text-gray-600">Belum ada riwayat broadcast</p>
                                        <p class="text-xs text-gray-400 mt-1">Gunakan tombol "+ Buat Broadcast Baru" untuk mulai mengirimkan pesan massal.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($campaigns, 'links'))
                    <div class="p-4 border-t border-gray-100 bg-white">
                        {{ $campaigns->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
