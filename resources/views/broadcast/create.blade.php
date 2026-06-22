<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kirim Broadcast (Pesan Massal)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-t-4 border-indigo-500">

                @if(session('success'))
                    <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                        <strong>Informasi:</strong> {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <strong>Error:</strong> {{ session('error') }}
                    </div>
                @endif

                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <p class="text-sm text-blue-800">
                        <strong>Tips Keamanan:</strong> Sistem akan memberi jeda otomatis selama 1,5 detik per pesan untuk menghindari pemblokiran dari pihak WhatsApp. Mohon <b>jangan tutup halaman ini</b> selama proses loading pengiriman berlangsung.
                    </p>
                </div>

                <form action="{{ route('broadcast.store') }}" method="POST" id="broadcastForm">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">1. Pilih Device Pengirim</label>
                            <select name="device_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">-- Pilih Device Terkoneksi --</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">
                                        {{ $device->name }} ({{ $device->phone_number ?? 'Nomor belum diset' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">2. Pilih Grup Tujuan</label>
                            <select name="category" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">-- Pilih Grup Target --</option>
                                <option value="all" class="font-bold text-indigo-600">» Kirim ke SEMUA Kontak «</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}">Grup: {{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">3. Isi Pesan Broadcast</label>
                        <textarea name="message" rows="6" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Ketik pengumuman atau pesan massal Anda di sini..." required></textarea>
                    </div>

                    <button type="submit" id="btnSubmit" onclick="this.innerHTML='Mengirim Broadcast... Mohon Tunggu'; this.classList.add('opacity-70', 'cursor-not-allowed');" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-md shadow-lg transition text-lg">
                        🚀 Mulai Kirim Broadcast
                    </button>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
