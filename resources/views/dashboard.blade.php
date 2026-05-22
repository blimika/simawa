<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if(auth()->user()->isSuperadmin())
                {{ __('Dashboard Superadmin') }}
            @elseif(auth()->user()->isAdmin())
                {{ __('Dashboard Admin') }}
            @else
                {{ __('Workspace Agen') }}
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if(auth()->user()->isSuperadmin())
                        <!-- Tampilan Khusus Superadmin -->
                        <div class="border-l-4 border-purple-500 pl-4 mb-4">
                            <h3 class="text-lg font-bold text-purple-700">Selamat datang, Super Administrator!</h3>
                            <p class="text-gray-600">Anda memiliki kendali penuh atas sistem SIMAWA. Anda dapat memantau seluruh aktivitas Admin, Agen, dan status koneksi semua Device WhatsApp.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                            <div class="bg-purple-50 p-4 rounded-lg border border-purple-100">
                                <h4 class="font-semibold text-purple-800">Total Device</h4>
                                <p class="text-2xl font-bold">{{ \App\Models\Device::count() }}</p>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                                <h4 class="font-semibold text-blue-800">Total Admin</h4>
                                <p class="text-2xl font-bold">{{ \App\Models\User::where('role', 'admin')->count() }}</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                                <h4 class="font-semibold text-green-800">Total Agen</h4>
                                <p class="text-2xl font-bold">{{ \App\Models\User::where('role', 'agent')->count() }}</p>
                            </div>
                        </div>

                    @elseif(auth()->user()->isAdmin())
                        <!-- Tampilan Khusus Admin -->
                        <div class="border-l-4 border-blue-500 pl-4 mb-4">
                            <h3 class="text-lg font-bold text-blue-700">Selamat datang, {{ auth()->user()->name }}!</h3>
                            <p class="text-gray-600">Ini adalah area manajemen Anda. Anda dapat mengelola Device WhatsApp dan mendaftarkan Agen baru untuk membantu membalas pesan.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                                <h4 class="font-semibold text-blue-800">Device Aktif Anda</h4>
                                <p class="text-2xl font-bold">{{ auth()->user()->devices()->count() ?? 0 }}</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg border border-green-100">
                                <h4 class="font-semibold text-green-800">Agen Anda</h4>
                                <p class="text-2xl font-bold">{{ auth()->user()->subordinates()->count() }}</p>
                            </div>
                        </div>

                    @else
                        <!-- Tampilan Khusus Agent -->
                        <div class="border-l-4 border-green-500 pl-4 mb-4">
                            <h3 class="text-lg font-bold text-green-700">Selamat bertugas, Agen {{ auth()->user()->name }}!</h3>
                            <p class="text-gray-600">Silakan buka menu <strong>Live Chat</strong> di atas untuk mulai membalas pesan pelanggan dari Device yang telah ditugaskan kepada Anda.</p>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
