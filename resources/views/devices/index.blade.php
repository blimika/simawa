<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Manajemen Device') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Kelola sesi gateway WhatsApp, status koneksi, dan penugasan akses live chat.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @if(auth()->user()->isSuperadmin())
                    <form action="{{ route('devices.sync') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mensinkronisasikan seluruh session device dari GOWA?');" class="inline">
                        @csrf
                        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition duration-150 shadow-sm inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 12H16"></path></svg>
                            Sinkronisasi Device GOWA
                        </button>
                    </form>
                @endif
                <a href="{{ route('devices.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg text-sm transition duration-150 shadow-sm inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    + Tambah Device
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8">

            <!-- Alert Success -->
            @if(session('success'))
                <div class="mb-5 bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3.5 rounded-xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
                </div>
            @elseif(session('error'))
                <div class="mb-5 bg-red-50 border border-red-300 text-red-800 px-4 py-3.5 rounded-xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">&times;</button>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/80 text-gray-600 uppercase text-xs font-semibold tracking-wider border-b border-gray-200">
                                <th class="py-3.5 px-6">Nama Device</th>
                                <th class="py-3.5 px-6">Akun WhatsApp Terhubung</th>
                                <th class="py-3.5 px-6">Session ID</th>
                                <th class="py-3.5 px-6">Admin / Pengelola</th>
                                <th class="py-3.5 px-6 text-center">Live Chat</th>
                                <th class="py-3.5 px-6">Status</th>
                                <th class="py-3.5 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm divide-y divide-gray-100">
                            @forelse($devices as $device)
                            <tr class="hover:bg-gray-50/80 transition">
                                {{-- Nama Device --}}
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <div class="font-bold text-gray-900 text-sm">{{ $device->name }}</div>
                                    <div class="text-[11px] text-gray-400 font-mono">Dibuat: {{ $device->created_at ? $device->created_at->format('d M Y') : '-' }}</div>
                                </td>

                                {{-- Akun WhatsApp Terhubung (Nama & Avatar Profil) --}}
                                <td class="py-4 px-6 whitespace-nowrap">
                                    @if($device->status === 'connected')
                                        <div class="flex items-center gap-3">
                                            {{-- Avatar Profil WhatsApp --}}
                                            <div class="relative shrink-0">
                                                @if(!empty($device->whatsapp_avatar))
                                                    <img src="{{ $device->whatsapp_avatar }}" 
                                                         alt="{{ $device->whatsapp_name ?? $device->phone_number }}" 
                                                         class="w-10 h-10 rounded-full object-cover border-2 border-emerald-400 shadow-sm"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 font-bold text-xs uppercase items-center justify-center border-2 border-emerald-300 shadow-sm" style="display: none;">
                                                        {{ strtoupper(substr($device->whatsapp_name ?: ($device->phone_number ?: 'WA'), 0, 2)) }}
                                                    </div>
                                                @else
                                                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs uppercase border-2 border-emerald-300 shadow-sm">
                                                        {{ strtoupper(substr($device->whatsapp_name ?: ($device->phone_number ?: 'WA'), 0, 2)) }}
                                                    </div>
                                                @endif
                                                {{-- Indikator Titik Hijau Terhubung --}}
                                                <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full"></span>
                                            </div>

                                            {{-- Detail Nama Akun & Nomor WhatsApp --}}
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="font-bold text-gray-900 text-sm">
                                                        {{ $device->whatsapp_name ?: 'Akun WhatsApp' }}
                                                    </span>
                                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-700">
                                                        WA
                                                    </span>
                                                </div>
                                                <div class="text-xs font-mono text-gray-600 mt-0.5">
                                                    +{{ $device->phone_number }}
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-3 opacity-60">
                                            <div class="w-10 h-10 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center font-bold text-xs border border-gray-200 shrink-0">
                                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                            </div>
                                            <div>
                                                <div class="text-xs font-mono text-gray-700 font-medium">
                                                    {{ $device->phone_number ? '+' . $device->phone_number : 'Nomor belum terisi' }}
                                                </div>
                                                <div class="text-[11px] text-amber-600 font-medium">
                                                    Belum Terhubung
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                {{-- Session ID --}}
                                <td class="py-4 px-6 whitespace-nowrap">
                                    <span class="font-mono text-xs bg-gray-100 text-gray-700 rounded px-2.5 py-1 border border-gray-200">
                                        {{ $device->gowa_session_id }}
                                    </span>
                                </td>

                                {{-- Admin / Pengelola --}}
                                <td class="py-4 px-6 whitespace-nowrap text-sm text-gray-900">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        <span class="font-medium text-gray-700">
                                            {{ $device->owner->name ?? 'Belum Ditugaskan' }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Live Chat --}}
                                <td class="py-4 px-6 whitespace-nowrap text-center">
                                    @if($device->is_livechat_enabled)
                                        <span class="bg-indigo-100 text-indigo-700 border border-indigo-200 px-2.5 py-0.5 rounded text-[10px] uppercase font-bold tracking-wide shadow-sm">
                                            Enabled
                                        </span>
                                    @else
                                        <span class="bg-gray-100 text-gray-500 border border-gray-300 px-2.5 py-0.5 rounded text-[10px] uppercase font-bold tracking-wide shadow-sm" title="Hanya meneruskan webhook ke server lain">
                                            Disabled
                                        </span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="py-4 px-6 whitespace-nowrap">
                                    @if($device->status == 'connected')
                                        <span class="inline-flex items-center gap-1.5 bg-emerald-100 text-emerald-800 border border-emerald-200 py-1 px-3 rounded-full text-xs font-semibold">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Terhubung
                                        </span>
                                    @elseif($device->status == 'disconnected')
                                        <span class="inline-flex items-center gap-1.5 bg-rose-100 text-rose-800 border border-rose-200 py-1 px-3 rounded-full text-xs font-semibold">
                                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                            Terputus
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 bg-amber-100 text-amber-800 border border-amber-200 py-1 px-3 rounded-full text-xs font-semibold">
                                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                            Pending
                                        </span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="py-4 px-6 text-center">
                                    <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                        <!-- 1. Tombol Cek Status -->
                                        <form action="{{ route('devices.check_status', $device->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 py-1.5 px-2.5 rounded-lg font-semibold transition border border-gray-200" title="Sinkronkan status & profil dengan GOWA">
                                                Cek Status
                                            </button>
                                        </form>

                                        @if($device->status === 'connected' || $device->status === 'logged')
                                            <!-- Tombol Logout -->
                                            <form action="{{ route('devices.logout', $device->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin memutus sesi WhatsApp pada device ini?');">
                                                @csrf
                                                <button type="submit" class="text-xs bg-orange-50 hover:bg-orange-100 text-orange-700 border border-orange-200 py-1.5 px-2.5 rounded-lg font-semibold transition" title="Logout sesi WhatsApp di GOWA">
                                                    Logout
                                                </button>
                                            </form>
                                        @else
                                            <!-- Tombol Scan QR (Muncul jika pending / disconnected) -->
                                            <a href="{{ route('devices.scan', $device->id) }}" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-1.5 px-3 rounded-lg shadow-sm transition">
                                                Scan QR
                                            </a>

                                            <!-- Tombol Reconnect (Muncul jika disconnected) -->
                                            @if($device->status === 'disconnected')
                                                <form action="{{ route('devices.reconnect', $device->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-xs bg-yellow-50 hover:bg-yellow-100 text-yellow-800 border border-yellow-200 py-1.5 px-2.5 rounded-lg font-semibold transition">
                                                        Reconnect
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                        <!-- Pesan Otomatis -->
                                        <a href="{{ route('auto-replies.index', ['device_id' => $device->id]) }}" class="bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1 shadow-sm" title="Kustomisasi Pesan Otomatis Live Chat">
                                            <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                            Pesan
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('devices.edit', $device->id) }}" class="bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1 shadow-sm">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            Edit
                                        </a>

                                        <!-- Hapus -->
                                        <form action="{{ route('devices.destroy', $device->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus device ini? Semua data agen terkait akan dilepas.');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 py-1.5 px-2.5 rounded-lg text-xs font-semibold transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-gray-400 bg-gray-50">
                                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    <p class="font-medium text-gray-600">Belum ada device yang didaftarkan</p>
                                    <p class="text-xs text-gray-400 mt-1">Klik tombol "+ Tambah Device" untuk mendaftarkan device baru.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($devices, 'links'))
                    <div class="p-4 border-t border-gray-100 bg-white">
                        {{ $devices->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
