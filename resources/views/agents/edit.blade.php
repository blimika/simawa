<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('agents.index') }}" class="text-gray-500 hover:text-gray-700 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Agen: ') }} <span class="text-indigo-600">{{ $agent->name }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg shadow-sm">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6 bg-white border-b border-gray-100">
                    <div class="mb-5 border-b pb-3">
                        <h3 class="text-lg font-bold text-gray-800">Informasi Profil & Akses Device</h3>
                        <p class="text-sm text-gray-500">Perbarui nama, username, alamat email, serta hak akses device WhatsApp untuk agen ini.</p>
                    </div>

                    <form action="{{ route('agents.update', $agent->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_type" value="info">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                <input type="text" name="name" value="{{ old('name', $agent->name) }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Username <span class="text-xs text-gray-400 font-normal">(Untuk Login)</span></label>
                                <input type="text" name="username" value="{{ old('username', $agent->username) }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Alamat Email</label>
                                <input type="email" name="email" value="{{ old('email', $agent->email) }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-gray-50">
                            </div>
                        </div>

                        <!-- Pilihan Akses Device -->
                        <div class="mt-6 border-t border-gray-100 pt-6">
                            <div class="mb-3">
                                <label class="block font-bold text-sm text-gray-800">Pilih Akses Device (Opsional)</label>
                                <p class="text-xs text-gray-500 mt-0.5">Tentukan device WhatsApp mana saja yang bisa diakses oleh agen ini untuk melihat dan membalas pesan di Live Chat.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @forelse($devices as $device)
                                    <label class="flex items-center justify-between p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                        <div class="flex items-center">
                                            <input type="checkbox" name="devices[]" value="{{ $device->id }}" 
                                                {{ in_array($device->id, old('devices', $assignedDeviceIds)) ? 'checked' : '' }} 
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                            <div class="ml-2">
                                                <span class="text-sm text-gray-800 font-medium block">{{ $device->name }}</span>
                                                @if($device->phone_number)
                                                    <span class="text-xs text-gray-500 font-mono">{{ $device->phone_number }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($device->status === 'connected')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-green-100 text-green-800">
                                                Terhubung
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-600">
                                                Terputus
                                            </span>
                                        @endif
                                    </label>
                                @empty
                                    <div class="col-span-2 text-sm text-amber-600 italic bg-amber-50 border border-amber-200 rounded-lg p-3">
                                        Belum ada device WhatsApp yang terdaftar. Silakan daftarkan device terlebih dahulu pada menu Device.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-bold text-white text-sm hover:bg-indigo-700 shadow-sm transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6 bg-white border-b border-gray-100">
                    <div class="mb-5 border-b pb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Reset Password</h3>
                            <p class="text-sm text-gray-500">Kosongkan bagian ini jika Anda tidak ingin mengubah password agen.</p>
                        </div>
                    </div>

                    <form action="{{ route('agents.update', $agent->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_type" value="password">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Password Baru</label>
                                <input type="password" name="password" required placeholder="Minimal 4 karakter"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" required placeholder="Ketik ulang password baru"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 sm:text-sm">
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button type="submit" onclick="return confirm('Yakin ingin mereset password agen ini?')" class="px-6 py-2 bg-amber-500 border border-transparent rounded-md font-bold text-white text-sm hover:bg-amber-600 shadow-sm transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
