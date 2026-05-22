<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Scan QR Code GOWA') }} - {{ $device->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg text-center p-8">

                <h3 class="text-lg font-bold text-gray-800 mb-2">Status: <span id="status-text" class="text-blue-600">Menyiapkan Halaman...</span></h3>
                <p class="text-sm text-gray-500 mb-6">Buka WhatsApp di HP Anda > Perangkat Tertaut > Tautkan Perangkat. Arahkan kamera ke QR Code di bawah ini.</p>

                <div class="flex justify-center mb-6 min-h-[256px] items-center">
                    <!-- Area Loading -->
                    <div id="loading-area" class="text-gray-400 flex flex-col items-center">
                        <svg class="animate-spin h-8 w-8 text-blue-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memuat data dari server...</span>
                    </div>

                    <!-- Area QR Code (Awalnya Disembunyikan) -->
                    <div id="qr-area" class="hidden p-4 border-4 border-gray-100 rounded-xl bg-white shadow-sm">
                        <img id="qr-image" src="" alt="QR Code WhatsApp" class="w-64 h-64 object-contain">
                    </div>

                    <!-- Area Sukses (Awalnya Disembunyikan) -->
                    <div id="success-area" class="hidden bg-green-100 border border-green-400 text-green-700 p-6 rounded-lg font-semibold w-full">
                        <div class="text-4xl mb-2">✅</div>
                        Device Terhubung dan Siap Digunakan!
                    </div>
                </div>

                <div class="flex justify-center gap-4 mt-6">
                    <button onclick="requestNewQr()" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md shadow text-sm font-medium transition duration-150">
                        Request Ulang QR Code
                    </button>
                    <a href="{{ route('devices.index') }}" class="py-2 px-4 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium transition duration-150">
                        Kembali ke Daftar
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- Script AJAX Murni (HTTP Polling) -->
    <script>
        // Deklarasi Elemen DOM
        const statusText = document.getElementById('status-text');
        const loadingArea = document.getElementById('loading-area');
        const qrArea = document.getElementById('qr-area');
        const qrImage = document.getElementById('qr-image');
        const successArea = document.getElementById('success-area');

        let pollingInterval;

        // Fungsi 1: Meminta QR Code saat halaman dimuat atau tombol ditekan
        function requestNewQr() {
            loadingArea.classList.remove('hidden');
            qrArea.classList.add('hidden');
            successArea.classList.add('hidden');
            statusText.innerText = "Meminta QR Code ke GOWA...";
            statusText.className = "text-yellow-600";

            if(pollingInterval) clearInterval(pollingInterval);

            fetch("{{ route('devices.request_qr', $device->id) }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            })
            .then(res => res.json())
            .then(resData => {
                console.log('Response Final:', resData);

                // resData.data adalah JSON asli dari GOWA
                const gowaJson = resData.data;

                if (resData.status === 'success' && gowaJson && gowaJson.code === 'SUCCESS') {

                    // Cek jika ada qr_link
                    if (gowaJson.results && gowaJson.results.qr_link) {
                        loadingArea.classList.add('hidden');
                        qrArea.classList.remove('hidden');

                        // Masukkan URL gambar langsung
                        qrImage.src = gowaJson.results.qr_link;

                        statusText.innerText = "Silakan Scan QR Code (Waktu: 30 Detik)";
                        statusText.className = "text-blue-600";

                        startPolling();
                    }
                    // Jika pesannya menyebutkan success/logged in tanpa QR
                    else if (gowaJson.message && gowaJson.message.toLowerCase().includes('success')) {
                        showSuccess();
                    }
                    else {
                        statusText.innerText = "Tidak ada gambar QR Code.";
                        statusText.className = "text-red-600";
                    }
                } else {
                    statusText.innerText = "Gagal memuat dari server GOWA.";
                    statusText.className = "text-red-600";
                }
            })
            .catch(err => {
                console.error('Error Fetch:', err);
                statusText.innerText = "Kesalahan Koneksi Jaringan";
                statusText.className = "text-red-600";
            });
        }

        // Fungsi 2: Bertanya ke server secara berkala (Polling)
        function startPolling() {
            console.log("Memulai polling pengecekan status login...");

            pollingInterval = setInterval(() => {
                fetch("{{ route('devices.status_json', $device->id) }}", {
                    method: "GET",
                    headers: {
                        "Accept": "application/json"
                    }
                })
                .then(res => res.json())
                .then(data => {
                    // PERUBAHAN: Gunakan data.status, bukan data.state
                    console.log("Status GOWA Terkini:", data.status);

                    // PERUBAHAN: Cek 'connected', karena Laravel mengirimnya sebagai 'connected'
                    if (data.status === 'connected') {
                        clearInterval(pollingInterval); // Hentikan polling
                        showSuccess(); // Tampilkan notifikasi sukses / redirect
                    }
                })
                .catch(err => console.error("Error saat polling:", err));

            }, 3000); // Mengecek setiap 3000 milidetik (3 detik)
        }

        // Fungsi 3: Tampilan sukses
        function showSuccess() {
            qrArea.classList.add('hidden');
            loadingArea.classList.add('hidden');
            successArea.classList.remove('hidden');
            statusText.innerText = "Terhubung (Logged In)";
            statusText.className = "text-green-600";
        }

        // Jalankan request QR secara otomatis saat halaman pertama kali dibuka
        document.addEventListener('DOMContentLoaded', requestNewQr);
    </script>
</x-app-layout>
