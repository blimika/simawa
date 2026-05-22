<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Superadmin') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="border-l-4 border-purple-500 pl-4 mb-4">
                        <h3 class="text-lg font-bold text-purple-700">Selamat datang, Super Administrator!</h3>
                        <p class="text-gray-600">Anda memiliki kendali penuh atas sistem SIMAWA. Anda dapat memantau seluruh aktivitas Admin, Agen, dan status koneksi semua Device WhatsApp.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                        <div class="bg-purple-50 p-4 rounded-lg border border-purple-100 shadow-sm">
                            <h4 class="font-semibold text-purple-800">Total Device</h4>
                            <p class="text-3xl font-bold mt-2">{{ \App\Models\Device::count() }}</p>
                        </div>
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 shadow-sm">
                            <h4 class="font-semibold text-blue-800">Total Admin</h4>
                            <p class="text-3xl font-bold mt-2">{{ \App\Models\User::where('role', 'admin')->count() }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg border border-green-100 shadow-sm">
                            <h4 class="font-semibold text-green-800">Total Agen</h4>
                            <p class="text-3xl font-bold mt-2">{{ \App\Models\User::where('role', 'agent')->count() }}</p>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h4 class="font-bold text-gray-700 mb-4">Aksi Cepat</h4>
                        <div class="flex gap-4">
                            <a href="{{ route('devices.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md text-sm font-medium transition">
                                Kelola Device WhatsApp
                            </a>
                            <a href="{{ route('agents.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-md text-sm font-medium transition">
                                Kelola Manajemen Agen
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
