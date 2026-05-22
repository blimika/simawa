<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ganti Password Agen: ') }} <span class="text-blue-600">{{ $agent->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <div class="mb-6 bg-gray-50 p-4 rounded-md border border-gray-200">
                    <p class="text-sm text-gray-600">Username Login: <strong class="text-gray-900">{{ $agent->username }}</strong></p>
                    <p class="text-sm text-gray-600">Email: <strong class="text-gray-900">{{ $agent->email }}</strong></p>
                </div>

                <form method="POST" action="{{ route('agents.update', $agent->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="password" class="block font-medium text-sm text-gray-700">Password Baru</label>
                        <input id="password" class="block mt-1 w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" type="password" name="password" required autofocus />
                        @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-6 border-b border-gray-200 pb-6">
                        <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Konfirmasi Password Baru</label>
                        <input id="password_confirmation" class="block mt-1 w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" type="password" name="password_confirmation" required />
                    </div>

                    <div class="flex items-center justify-end">
                        <a href="{{ route('agents.index') }}" class="underline text-sm text-gray-600 hover:text-gray-900 mr-4">Batal</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow transition duration-150 text-sm">
                            Update Password
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
