<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tautkan WhatsApp (GOWA v9)') }} - {{ $device->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg text-center p-8">

                <!-- Tab Navigasi Pilihan Metode -->
                <div class="flex border-b border-gray-200 mb-6 justify-center">
                    <button type="button" id="tab-qr-btn" onclick="switchTab('qr')" class="py-3 px-6 text-sm font-semibold border-b-2 border-blue-600 text-blue-600 transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        Scan QR Code
                    </button>
                    <button type="button" id="tab-code-btn" onclick="switchTab('code')" class="py-3 px-6 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        Kode Pairing Nomor HP
                    </button>
                </div>

                <!-- Status Header -->
                <h3 class="text-lg font-bold text-gray-800 mb-2">Status: <span id="status-text" class="text-blue-600">Menyiapkan Halaman...</span></h3>

                <!-- Area Sukses (Jika Device Terhubung) -->
                <div id="success-area" class="hidden bg-green-50 border-2 border-green-500 text-green-800 p-8 rounded-xl font-semibold mb-6">
                    <div class="text-5xl mb-3">✅</div>
                    <div class="text-xl font-bold mb-1">Device Berhasil Terhubung!</div>
                    <p class="text-sm text-green-700 font-normal mb-4">Sesi WhatsApp aktif dan siap digunakan untuk gateway & live chat.</p>
                    <a href="{{ route('devices.index') }}" class="inline-block bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-md shadow transition">
                        Buka Daftar Device
                    </a>
                </div>

                <!-- Area Peringatan & Penanganan Passkey WhatsApp (GOWA PR #754) -->
                <div id="passkey-area" class="hidden bg-amber-50 border-2 border-amber-400 text-amber-950 p-6 rounded-xl w-full text-left mb-6 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="text-3xl">🔑</div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-1">
                                <h4 class="font-bold text-base text-amber-900">WhatsApp Meminta Verifikasi Passkey (Kunci Sandi)</h4>
                                <span class="bg-amber-200 text-amber-900 text-xs px-2.5 py-0.5 rounded-full font-semibold">Tantangan Keamanan Terdeteksi</span>
                            </div>
                            <p class="text-xs text-amber-800 leading-relaxed mb-3">
                                Layar WhatsApp di HP Anda meminta verifikasi <b>Passkey / Kunci Sandi</b> atau <i>"Lanjutkan di WhatsApp Web"</i>. WhatsApp server menunggu penandatanganan WebAuthn dari peramban Chrome Anda.
                            </p>

                            <!-- Status & Kontrol Ekstensi Browser wa-passkey-connector -->
                            <div id="passkey-extension-box" class="p-4 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-900 mb-3 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-sm text-blue-950 flex items-center gap-1.5">
                                        <span>⚡</span> Status Ekstensi Browser:
                                    </span>
                                    <span id="ext-status-badge" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-gray-200 text-gray-700">Mengecek Ekstensi...</span>
                                </div>
                                <p id="ext-status-desc" class="text-blue-800 leading-relaxed">
                                    Mendeteksi ekstensi <b>wa-passkey-connector</b> di Google Chrome...
                                </p>
                                <div class="pt-1 flex flex-wrap items-center gap-2">
                                    <button type="button" id="btn-manual-trigger-ext" onclick="manualTriggerPasskey()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 px-3.5 rounded shadow text-xs flex items-center gap-1.5 transition">
                                        <span>🚀</span>
                                        <span>Picu Pop-up Passkey di Browser Sekarang</span>
                                    </button>
                                    <button type="button" onclick="pingExtension()" class="bg-white hover:bg-gray-100 text-blue-800 border border-blue-300 py-1.5 px-3 rounded text-xs transition">
                                        🔄 Cek Ulang Status Ekstensi
                                    </button>
                                </div>
                                <div id="ext-help-tip" class="hidden text-[11px] bg-white p-3 rounded border border-blue-200 text-gray-700 mt-2 space-y-1">
                                    <p class="font-bold text-blue-900">💡 Ekstensi Belum Terdeteksi? Lakukan langkah ini:</p>
                                    <ol class="list-decimal pl-4 space-y-1 leading-relaxed">
                                        <li>Buka tab baru di browser Anda: <code>chrome://extensions</code></li>
                                        <li>Cari ekstensi <b>wa-passkey-connector</b> lalu klik tombol <b>Details</b> (Detail).</li>
                                        <li>Pada bagian <b>Site access</b> (Akses situs), pilih <b>"On all sites"</b> (Pada semua situs).</li>
                                        <li>Kembali ke halaman ini lalu klik tombol <b>"Cek Ulang Status Ekstensi"</b> di atas.</li>
                                    </ol>
                                </div>
                            </div>

                            <!-- Kotak Kode Konfirmasi Passkey (HANYA MUNCUL JIKA WHATSAPP MENGIRIM KODE ANGKA) -->
                            <div id="passkey-code-box" class="hidden my-3 p-4 bg-white rounded-lg border-2 border-amber-300 text-center shadow-sm">
                                <span class="text-xs font-bold text-gray-600 uppercase tracking-wider block mb-1">Kode Konfirmasi Passkey WhatsApp:</span>
                                <div id="passkey-code" class="text-3xl font-mono font-extrabold tracking-widest text-amber-900 my-2 select-all"></div>
                                <button id="btn-confirm-passkey" type="button" onclick="submitPasskeyConfirm()" class="bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold py-2 px-5 rounded-lg shadow transition flex items-center gap-2 mx-auto">
                                    <span id="passkey-confirm-spinner" class="hidden animate-spin h-3.5 w-3.5 border-2 border-white border-t-transparent rounded-full"></span>
                                    <span>Kirim Konfirmasi Kode ke WhatsApp</span>
                                </button>
                                <p class="text-[11px] text-gray-500 mt-1.5">Gunakan tombol ini jika WhatsApp menampilkan kode angka konfirmasi di atas.</p>
                            </div>

                            <!-- Opsi Pancingan Cadangan -->
                            <div class="bg-white border border-amber-200 p-3.5 rounded-lg text-xs text-gray-700 space-y-2 mb-3">
                                <p class="font-bold text-gray-900">🌐 Opsi Alternatif Tanpa Ekstensi (Trik Pancingan WhatsApp Web):</p>
                                <p class="text-gray-600 leading-relaxed">
                                    Buka tab baru dan akses <a href="https://web.whatsapp.com" target="_blank" class="text-blue-600 underline font-semibold">web.whatsapp.com</a> di browser ini. Login sekali di sana dan izinkan scan passkey. Setelah login lolos di web resmi, server WhatsApp akan menganggap perangkat ini tepercaya (*trusted*), lalu klik tombol "Request Ulang" di bawah.
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2 pt-1">
                                <button type="button" onclick="requestNewQr()" class="bg-white hover:bg-gray-100 text-gray-800 text-xs font-semibold py-2 px-4 rounded-lg border border-gray-300 transition">
                                    🔄 Reset &amp; Request Ulang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 1: SCAN QR CODE -->
                <div id="tab-qr-content">
                    <p class="text-sm text-gray-500 mb-6">Buka WhatsApp di HP Anda &gt; <b>Perangkat Tertaut</b> &gt; <b>Tautkan Perangkat</b>. Arahkan kamera ke QR Code di bawah ini.</p>

                    <div class="flex justify-center mb-6 min-h-[260px] items-center">
                        <!-- Loading QR -->
                        <div id="loading-area" class="text-gray-400 flex flex-col items-center">
                            <svg class="animate-spin h-8 w-8 text-blue-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Meminta QR Code ke server...</span>
                        </div>

                        <!-- Area QR Code -->
                        <div id="qr-area" class="hidden p-4 border-4 border-gray-100 rounded-xl bg-white shadow-sm flex flex-col items-center">
                            <img id="qr-image" src="" alt="QR Code WhatsApp" class="w-64 h-64 object-contain">
                            <span class="text-xs text-gray-400 mt-2">QR Code diperbarui otomatis jika kedaluwarsa</span>
                        </div>
                    </div>

                    <div class="flex justify-center gap-4 mt-6">
                        <button onclick="requestNewQr()" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md shadow text-sm font-medium transition">
                            Request Ulang QR Code
                        </button>
                        <a href="{{ route('devices.index') }}" class="py-2 px-4 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium transition">
                            Kembali ke Daftar
                        </a>
                    </div>
                </div>

                <!-- TAB 2: PAIRING CODE (LOGIN DENGAN NOMOR HP) -->
                <div id="tab-code-content" class="hidden text-left">
                    <p class="text-sm text-gray-600 mb-4">
                        Tautkan perangkat dengan memasukkan <b>Kode 8-Karakter WhatsApp</b> langsung di HP Anda. Metode ini <b>tidak membutuhkan pemindaian kamera</b> dan <b>tidak terhambat Passkey WebAuthn</b>.
                    </p>

                    <div class="bg-gray-50 p-5 rounded-xl border border-gray-200 mb-6">
                        <label for="pairing_phone" class="block text-sm font-semibold text-gray-700 mb-1">Nomor WhatsApp Anda:</label>
                        <div class="flex gap-2">
                            <input type="text" id="pairing_phone" value="{{ $device->phone_number }}" placeholder="Contoh: 6281234567890" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm font-mono">
                            <button type="button" id="btn-request-pair-code" onclick="requestPairingCode()" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-5 rounded-md shadow transition flex items-center gap-2">
                                <span id="pair-btn-spinner" class="hidden animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                                <span id="pair-btn-text">Dapatkan Kode</span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5">Gunakan format internasional (misal 628xxx). Angka 0 di depan akan otomatis disesuaikan menjadi 62.</p>
                    </div>

                    <!-- Area Hasil Kode Pairing -->
                    <div id="pair-code-result" class="hidden bg-blue-50 border-2 border-blue-400 rounded-xl p-6 mb-6 text-center">
                        <span class="text-xs font-semibold text-blue-700 uppercase tracking-wider block mb-2">Kode Penautan WhatsApp Anda:</span>
                        <div class="flex justify-center items-center gap-3 mb-3">
                            <div id="display-pair-code" class="text-3xl md:text-4xl font-mono font-bold tracking-widest text-blue-900 bg-white py-3 px-6 rounded-lg border-2 border-blue-200 shadow-inner select-all">
                                ---- - ----
                            </div>
                            <button type="button" onclick="copyPairCode()" title="Salin Kode" class="p-3 bg-white hover:bg-blue-100 rounded-lg border border-blue-300 text-blue-600 transition shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            </button>
                        </div>
                        <p id="copy-status" class="text-xs text-green-600 font-semibold mb-4 hidden">✅ Kode berhasil disalin ke clipboard!</p>

                        <div class="bg-white p-4 rounded-lg border border-blue-200 text-left text-xs text-gray-700 space-y-2">
                            <p class="font-bold text-gray-900">📲 Langkah-langkah Memasukkan Kode di Ponsel Anda:</p>
                            <ol class="list-decimal pl-5 space-y-1.5 leading-relaxed">
                                <li>Buka aplikasi <b>WhatsApp</b> di ponsel Anda.</li>
                                <li>Ketuk menu titik tiga (Android) atau <b>Setelan</b> (iPhone) &gt; <b>Perangkat tertaut</b>.</li>
                                <li>Ketuk tombol <b>Tautkan perangkat</b>.</li>
                                <li>Pada layar kamera scan QR, lihat bagian bawah dan ketuk <b>"Tautkan dengan nomor telepon saja"</b> (<i>Link with phone number instead</i>).</li>
                                <li>Ketikkan 8 karakter kode di atas ke layar ponsel Anda.</li>
                            </ol>
                            <p class="text-blue-700 font-medium pt-1">💡 Halaman ini akan mendeteksi otomatis saat Anda selesai memasukkan kode di ponsel.</p>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mt-6">
                        <button type="button" onclick="switchTab('qr')" class="text-sm text-blue-600 hover:underline font-medium">
                            ← Beralih ke Scan QR Code
                        </button>
                        <a href="{{ route('devices.index') }}" class="py-2 px-4 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 text-sm font-medium transition">
                            Kembali ke Daftar
                        </a>
                    </div>
                </div>

                <!-- Petunjuk Tambahan Mengenai Passkey -->
                <div class="mt-8 p-3.5 bg-gray-50 border border-gray-200 rounded-lg text-left text-xs text-gray-600 leading-relaxed">
                    <p class="font-semibold text-gray-800 mb-1">💡 Mengapa WhatsApp Meminta Passkey saat Scan QR?</p>
                    <p>WhatsApp versi terbaru memiliki proteksi <b>Passkey (Kunci Sandi WebAuthn)</b>. Pada gateway pihak ketiga, browser membatasi WebAuthn domain selain web resmi WhatsApp. Fitur <b>Kode Pairing Nomor HP</b> di atas adalah alternatif resmi dari WhatsApp yang didukung penuh oleh GOWA v9 untuk menghubungkan akun tanpa hambatan Passkey.</p>
                </div>

            </div>
        </div>
    </div>

    <!-- Script Interaktif -->
    <script>
        // Deklarasi Elemen DOM
        const statusText = document.getElementById('status-text');
        const loadingArea = document.getElementById('loading-area');
        const qrArea = document.getElementById('qr-area');
        const qrImage = document.getElementById('qr-image');
        const successArea = document.getElementById('success-area');
        const passkeyArea = document.getElementById('passkey-area');
        const passkeyCodeBox = document.getElementById('passkey-code-box');
        const passkeyCode = document.getElementById('passkey-code');

        const tabQrBtn = document.getElementById('tab-qr-btn');
        const tabCodeBtn = document.getElementById('tab-code-btn');
        const tabQrContent = document.getElementById('tab-qr-content');
        const tabCodeContent = document.getElementById('tab-code-content');

        const pairCodeResult = document.getElementById('pair-code-result');
        const displayPairCode = document.getElementById('display-pair-code');
        const pairingPhoneInput = document.getElementById('pairing_phone');
        const pairBtnSpinner = document.getElementById('pair-btn-spinner');
        const pairBtnText = document.getElementById('pair-btn-text');
        const copyStatus = document.getElementById('copy-status');

        let pollingInterval = null;
        let activeTab = 'qr';
        let rawPairCode = '';

        // Switch Tab
        function switchTab(tab) {
            activeTab = tab;
            if (tab === 'qr') {
                tabQrContent.classList.remove('hidden');
                tabCodeContent.classList.add('hidden');
                tabQrBtn.className = "py-3 px-6 text-sm font-semibold border-b-2 border-blue-600 text-blue-600 transition flex items-center gap-2";
                tabCodeBtn.className = "py-3 px-6 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition flex items-center gap-2";
                if (!qrImage.src) {
                    requestNewQr();
                } else {
                    startPolling();
                }
            } else {
                tabQrContent.classList.add('hidden');
                tabCodeContent.classList.remove('hidden');
                tabCodeBtn.className = "py-3 px-6 text-sm font-semibold border-b-2 border-blue-600 text-blue-600 transition flex items-center gap-2";
                tabQrBtn.className = "py-3 px-6 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 transition flex items-center gap-2";
                statusText.innerText = "Masukkan Nomor HP untuk Pairing";
                statusText.className = "text-blue-600";
                startPolling();
            }
        }

        function switchToPairingCodeAuto() {
            switchTab('code');
            const phoneVal = pairingPhoneInput.value.trim();
            if (phoneVal) {
                requestPairingCode();
            }
        }

        // Request QR Code
        function requestNewQr() {
            window._lastPasskeyChallenge = null;
            loadingArea.classList.remove('hidden');
            qrArea.classList.add('hidden');
            successArea.classList.add('hidden');
            if (passkeyArea) passkeyArea.classList.add('hidden');
            statusText.innerText = "Meminta QR Code ke GOWA...";
            statusText.className = "text-yellow-600";

            if (pollingInterval) clearInterval(pollingInterval);

            fetch("{{ route('devices.request_qr', $device->id) }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            })
            .then(res => res.json())
            .then(resData => {
                const gowaJson = resData.data;

                if (resData.status === 'already_logged_in') {
                    showSuccess();
                    return;
                }

                if (resData.status === 'success' && gowaJson && (gowaJson.code === 'SUCCESS' || gowaJson.status === 200)) {
                    if (gowaJson.results && gowaJson.results.qr_link) {
                        loadingArea.classList.add('hidden');
                        qrArea.classList.remove('hidden');
                        qrImage.src = gowaJson.results.qr_link;

                        statusText.innerText = "Silakan Scan QR Code (Waktu: 30 Detik)";
                        statusText.className = "text-blue-600";

                        startPolling();
                    } else if (gowaJson.results && gowaJson.results.is_logged_in === true) {
                        showSuccess();
                    } else {
                        statusText.innerText = (gowaJson && gowaJson.message) ? gowaJson.message : "Tidak ada QR Code.";
                        statusText.className = "text-red-600";
                    }
                } else {
                    statusText.innerText = "Gagal memuat QR Code dari server GOWA.";
                    statusText.className = "text-red-600";
                }
            })
            .catch(err => {
                console.error('Error Fetch QR:', err);
                statusText.innerText = "Kesalahan Koneksi Jaringan";
                statusText.className = "text-red-600";
            });
        }

        // Request Pairing Code
        function requestPairingCode() {
            window._lastPasskeyChallenge = null;
            const phoneVal = pairingPhoneInput.value.trim();
            if (!phoneVal) {
                alert("Silakan masukkan nomor WhatsApp Anda.");
                return;
            }

            pairBtnSpinner.classList.remove('hidden');
            pairBtnText.innerText = "Memproses...";
            statusText.innerText = "Meminta Kode Pairing ke WhatsApp...";
            statusText.className = "text-yellow-600";

            fetch("{{ route('devices.request_pairing_code', $device->id) }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify({ phone_number: phoneVal })
            })
            .then(res => res.json())
            .then(resData => {
                pairBtnSpinner.classList.add('hidden');
                pairBtnText.innerText = "Dapatkan Kode";

                if (resData.status === 'success' && resData.pair_code) {
                    rawPairCode = resData.pair_code;
                    displayPairCode.innerText = rawPairCode;
                    pairCodeResult.classList.remove('hidden');

                    statusText.innerText = "Kode Diterima! Masukkan Kode di WhatsApp Ponsel Anda";
                    statusText.className = "text-blue-600";

                    startPolling();
                } else {
                    alert(resData.message || "Gagal mendapatkan kode pairing dari server.");
                    statusText.innerText = "Gagal mendapatkan kode pairing.";
                    statusText.className = "text-red-600";
                }
            })
            .catch(err => {
                console.error('Error Request Pairing Code:', err);
                pairBtnSpinner.classList.add('hidden');
                pairBtnText.innerText = "Dapatkan Kode";
                alert("Terjadi kesalahan jaringan saat meminta kode pairing.");
            });
        }

        function copyPairCode() {
            if (!rawPairCode) return;
            navigator.clipboard.writeText(rawPairCode).then(() => {
                copyStatus.classList.remove('hidden');
                setTimeout(() => {
                    copyStatus.classList.add('hidden');
                }, 3000);
            }).catch(err => {
                console.error("Gagal menyalin:", err);
            });
        }

        let isExtensionDetected = false;
        let latestChallenge = null;
        let currentRequestId = null;

        // Deteksi Ekstensi wa-passkey-connector via PING
        function pingExtension() {
            const badge = document.getElementById('ext-status-badge');
            const desc = document.getElementById('ext-status-desc');
            const tip = document.getElementById('ext-help-tip');

            if (badge) {
                badge.className = "px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-yellow-100 text-yellow-800";
                badge.innerText = "Mengecek...";
            }

            window.postMessage({ target: 'wa-passkey-connector', type: 'PING' }, '*');

            setTimeout(() => {
                if (!isExtensionDetected) {
                    if (badge) {
                        badge.className = "px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-100 text-red-800";
                        badge.innerText = "⚠️ Belum Terdeteksi";
                    }
                    if (desc) {
                        desc.innerHTML = "Ekstensi <b>wa-passkey-connector</b> belum merespon di halaman ini. Pastikan izin akses situs telah diaktifkan.";
                    }
                    if (tip) tip.classList.remove('hidden');
                }
            }, 1200);
        }

        function updateExtensionStatus(detected) {
            isExtensionDetected = detected;
            const badge = document.getElementById('ext-status-badge');
            const desc = document.getElementById('ext-status-desc');
            const tip = document.getElementById('ext-help-tip');

            if (detected) {
                if (badge) {
                    badge.className = "px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-green-100 text-green-800";
                    badge.innerText = "🟢 Terhubung & Aktif";
                }
                if (desc) {
                    desc.innerHTML = "Ekstensi <b>wa-passkey-connector</b> terdeteksi aktif dan siap menjalankan verifikasi WebAuthn otomatis!";
                }
                if (tip) tip.classList.add('hidden');
            }
        }

        function sendPasskeyToExtension(challengeData) {
            if (!challengeData) return;
            latestChallenge = challengeData;
            currentRequestId = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : (Date.now() + '-' + Math.random().toString(36).substring(2));

            let publicKey = challengeData;
            if (typeof challengeData === 'string') {
                try {
                    publicKey = JSON.parse(challengeData);
                } catch (e) {
                    publicKey = challengeData;
                }
            }

            console.log("Mengirim pesan ke wa-passkey-connector:", {
                target: 'wa-passkey-connector',
                type: 'RUN_PASSKEY_ASSERTION',
                requestId: currentRequestId,
                publicKey: publicKey
            });

            window.postMessage({
                target: 'wa-passkey-connector',
                type: 'RUN_PASSKEY_ASSERTION',
                requestId: currentRequestId,
                publicKey: publicKey
            }, '*');
        }

        function manualTriggerPasskey() {
            if (!latestChallenge) {
                alert("Belum ada tantangan passkey aktif dari WhatsApp server. Pastikan WhatsApp di ponsel Anda sudah memunculkan permintaan verifikasi.");
                return;
            }
            statusText.innerText = "Memicu verifikasi passkey ke ekstensi browser...";
            statusText.className = "text-blue-600 font-bold";
            sendPasskeyToExtension(latestChallenge);
        }

        // Polling Status Login
        function startPolling() {
            if (pollingInterval) clearInterval(pollingInterval);

            pollingInterval = setInterval(() => {
                fetch("{{ route('devices.status_json', $device->id) }}", {
                    method: "GET",
                    headers: { "Accept": "application/json" }
                })
                .then(res => res.json())
                .then(data => {
                    console.log("Status GOWA Terkini:", data.status, data.passkey);

                    if (data.status === 'connected') {
                        clearInterval(pollingInterval);
                        showSuccess();
                    } else if (data.passkey) {
                        // Tampilkan modal/banner passkey di kedua tab (QR maupun Kode Pairing)
                        if (passkeyArea) passkeyArea.classList.remove('hidden');
                        statusText.innerText = "WhatsApp Meminta Verifikasi Passkey (Kunci Sandi)";
                        statusText.className = "text-amber-600 font-bold";

                        // 1. Jika WhatsApp mengirimkan kode verifikasi angka konfirmasi
                        if (data.passkey.code) {
                            if (passkeyCodeBox) passkeyCodeBox.classList.remove('hidden');
                            if (passkeyCode) passkeyCode.innerText = data.passkey.code;
                        } else {
                            if (passkeyCodeBox) passkeyCodeBox.classList.add('hidden');
                        }

                        // 2. Jika ada challenge WebAuthn, siarkan ke ekstensi wa-passkey-connector
                        if (data.passkey.challenge) {
                            latestChallenge = data.passkey.challenge;
                            if (window._lastPasskeyChallenge !== JSON.stringify(data.passkey.challenge)) {
                                window._lastPasskeyChallenge = JSON.stringify(data.passkey.challenge);
                                sendPasskeyToExtension(data.passkey.challenge);
                            }
                        }
                    }
                })
                .catch(err => console.error("Error saat polling:", err));
            }, 3000);
        }

        function submitPasskeyConfirm() {
            const btnSpinner = document.getElementById('passkey-confirm-spinner');
            if (btnSpinner) btnSpinner.classList.remove('hidden');
            statusText.innerText = "Mengirim konfirmasi kode passkey ke server WhatsApp...";
            statusText.className = "text-yellow-600 font-bold";

            fetch("{{ route('devices.passkey_confirm', $device->id) }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            })
            .then(res => res.json())
            .then(resData => {
                if (btnSpinner) btnSpinner.classList.add('hidden');
                if (resData.status === 'success') {
                    statusText.innerText = "Konfirmasi terkirim! Menunggu WhatsApp mengizinkan sesi...";
                    statusText.className = "text-green-600 font-bold";
                    startPolling();
                } else {
                    alert(resData.message || "Gagal konfirmasi passkey.");
                    statusText.innerText = "Gagal konfirmasi passkey: " + (resData.message || '');
                    statusText.className = "text-red-600 font-bold";
                }
            })
            .catch(err => {
                if (btnSpinner) btnSpinner.classList.add('hidden');
                alert("Kesalahan koneksi saat konfirmasi passkey.");
            });
        }

        function showSuccess() {
            if (tabQrContent) tabQrContent.classList.add('hidden');
            if (tabCodeContent) tabCodeContent.classList.add('hidden');
            if (qrArea) qrArea.classList.add('hidden');
            if (loadingArea) loadingArea.classList.add('hidden');
            if (passkeyArea) passkeyArea.classList.add('hidden');
            successArea.classList.remove('hidden');
            statusText.innerText = "Terhubung (Logged In)";
            statusText.className = "text-green-600 font-bold";
        }

        // Listener jika ada browser extension (seperti wa-passkey-connector)
        window.addEventListener('message', (event) => {
            // Tanggapan PING/PONG dari ekstensi
            if (event.data && (event.data.source === 'wa-passkey-connector' || event.data.target === 'wa-passkey-connector-client')) {
                if (event.data.type === 'PONG') {
                    console.log("Ekstensi wa-passkey-connector merespon PONG!");
                    updateExtensionStatus(true);
                    return;
                }
            }

            // Tanggapan PASSKEY_ASSERTION_RESULT
            if (event.data && (event.data.source === 'wa-passkey-connector' || event.data.type === 'PASSKEY_ASSERTION_RESULT')) {
                if (event.data.type === 'PASSKEY_ASSERTION_RESULT') {
                    if (event.data.error) {
                        console.error("Error assertion dari ekstensi:", event.data.error);
                        statusText.innerText = "Verifikasi passkey gagal di peramban: " + (event.data.error.message || event.data.error);
                        statusText.className = "text-red-600 font-bold";
                        return;
                    }

                    if (event.data.assertion) {
                        console.log("Menerima Passkey Assertion dari Ekstensi Browser:", event.data.assertion);
                        statusText.innerText = "Passkey terverifikasi di peramban! Mengirim ke server GOWA...";
                        statusText.className = "text-blue-600 font-bold";

                        fetch("{{ route('devices.passkey_response', $device->id) }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Content-Type": "application/json",
                                "Accept": "application/json"
                            },
                            body: JSON.stringify(event.data.assertion)
                        })
                        .then(res => res.json())
                        .then(resData => {
                            console.log('Passkey Assertion Response:', resData);
                            if (resData.status === 'success') {
                                statusText.innerText = "Passkey berhasil diterima server WhatsApp! Menghubungkan...";
                                statusText.className = "text-green-600 font-bold";
                                startPolling();
                            } else {
                                statusText.innerText = "Gagal memproses passkey: " + (resData.message || '');
                                statusText.className = "text-red-600 font-bold";
                            }
                        })
                        .catch(err => {
                            console.error('Error kirim assertion:', err);
                            statusText.innerText = "Gagal mengirim passkey assertion ke server.";
                            statusText.className = "text-red-600 font-bold";
                        });
                    }
                }
            }
        });

        // Jalankan saat pertama dimuat
        document.addEventListener('DOMContentLoaded', () => {
            pingExtension();
            requestNewQr();
        });
    </script>
</x-app-layout>
