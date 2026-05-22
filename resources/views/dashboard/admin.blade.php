<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Admin SIMAWA') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    Selamat datang, Admin {{ auth()->user()->name }}! (UID: {{ auth()->user()->user_uid }})
                </div>
            </div>

            <!-- Kartu Statistik Singkat -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-gray-500 text-sm font-medium">Total Device</h3>
                    <p class="text-3xl font-bold text-gray-800">0</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-gray-500 text-sm font-medium">Total Agen</h3>
                    <p class="text-3xl font-bold text-gray-800">0</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-gray-500 text-sm font-medium">Pesan Hari Ini</h3>
                    <p class="text-3xl font-bold text-gray-800">0</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
