<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center gap-2">
                    <span>📖</span> {{ __('Dokumentasi REST API SIMAWA') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Panduan lengkap integrasi pengiriman pesan WhatsApp otomatis via API Key Device.</p>
            </div>
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 border border-purple-200 shadow-sm">
                    🔒 Khusus Administrator
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-10" x-data="{
        selectedDeviceKey: '{{ $devices->first()->api_key ?? 'YOUR_API_KEY_HERE' }}',
        selectedDeviceName: '{{ $devices->first()->name ?? '' }}',
        activeTab: 'curl',
        copied: false,
        copiedEndpoint: false,
        showKey: false,
        baseUrl: '{{ $baseUrl }}',
        copyText(text, type = 'key') {
            navigator.clipboard.writeText(text);
            if (type === 'key') {
                this.copied = true;
                setTimeout(() => this.copied = false, 2500);
            } else if (type === 'endpoint') {
                this.copiedEndpoint = true;
                setTimeout(() => this.copiedEndpoint = false, 2500);
            }
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- 1. KOTAK PEMILIHAN DEVICE & API KEY AKTIF -->
            <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-indigo-800 rounded-2xl shadow-lg p-6 sm:p-8 text-white relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
                    <svg class="w-72 h-72" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                </div>

                <div class="relative z-10">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
                        <div>
                            <span class="inline-block bg-blue-500/30 text-blue-200 text-xs font-semibold px-2.5 py-0.5 rounded-full border border-blue-400/30 mb-2">
                                🔑 Otorisasi Cepat
                            </span>
                            <h3 class="text-xl sm:text-2xl font-bold">Kunci Otentikasi Device Anda</h3>
                            <p class="text-blue-100 text-sm mt-1">Pilih device di bawah ini untuk langsung memuat API Key pada contoh kode.</p>
                        </div>

                        <!-- Dropdown Pilihan Device -->
                        @if($devices->count() > 0)
                            <div class="w-full md:w-72">
                                <label class="block text-xs font-medium text-blue-200 mb-1">Ganti Pilihan Device:</label>
                                <select @change="
                                    let opt = $event.target.options[$event.target.selectedIndex];
                                    selectedDeviceKey = opt.dataset.key;
                                    selectedDeviceName = opt.dataset.name;
                                " class="w-full bg-white/10 border border-white/20 rounded-lg py-2 px-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-blue-400 focus:bg-indigo-900 transition cursor-pointer">
                                    @foreach($devices as $dev)
                                        <option value="{{ $dev->id }}" data-key="{{ $dev->api_key }}" data-name="{{ $dev->name }}" class="text-gray-900">
                                            {{ $dev->name }} ({{ $dev->status === 'connected' ? '🟢 Connected' : '🔴 Disconnected' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>

                    @if($devices->count() > 0)
                        <!-- Tampilan API Key Bar -->
                        <div class="bg-black/30 backdrop-blur-md rounded-xl p-4 border border-white/10">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs uppercase tracking-wider text-blue-200 font-semibold">Active Device API Key:</span>
                                        <span x-text="selectedDeviceName" class="text-xs px-2 py-0.5 rounded bg-blue-500/40 text-white font-medium"></span>
                                    </div>
                                    <div class="font-mono text-sm sm:text-base font-bold text-yellow-300 break-all select-all flex items-center gap-2">
                                        <span x-text="showKey ? selectedDeviceKey : selectedDeviceKey.substring(0, 8) + '••••••••••••••••••••••••' + selectedDeviceKey.substring(selectedDeviceKey.length - 4)"></span>
                                        <button type="button" @click="showKey = !showKey" class="text-blue-200 hover:text-white transition p-1" title="Lihat/Sembunyikan">
                                            <svg x-show="!showKey" class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <svg x-show="showKey" class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <button type="button" @click="copyText(selectedDeviceKey, 'key')" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-lg bg-white text-indigo-700 hover:bg-blue-50 font-bold text-xs shadow transition active:scale-95">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        <span x-text="copied ? 'Tersalin!' : 'Salin API Key'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-amber-500/20 border border-amber-300/40 rounded-xl p-4 text-amber-100 flex items-center justify-between gap-4">
                            <p class="text-sm">⚠️ Anda belum memiliki device WhatsApp yang terdaftar di SIMAWA.</p>
                            <a href="{{ route('devices.create') }}" class="px-3 py-1.5 bg-white text-indigo-700 font-bold text-xs rounded-lg shadow hover:bg-gray-100 transition shrink-0">
                                + Tambah Device Sekarang
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 2. ENDPOINT & FORMAT HEADER -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="font-bold text-base text-gray-800 flex items-center gap-2">
                        <span class="text-blue-600">🌐</span> Endpoint Utama
                    </h3>
                    <span class="text-xs text-gray-500 font-mono">RESTful API / JSON Response</span>
                </div>

                <div class="p-6">
                    <div class="flex flex-col md:flex-row md:items-center gap-3 bg-gray-900 rounded-xl p-4 text-white font-mono text-sm">
                        <span class="px-3 py-1 bg-emerald-600 text-white font-bold rounded-md text-xs self-start md:self-auto tracking-wide">POST</span>
                        <span class="flex-1 text-emerald-400 break-all select-all" x-text="baseUrl + '/api/send'"></span>
                        <button type="button" @click="copyText(baseUrl + '/api/send', 'endpoint')" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-md text-xs font-sans text-gray-300 hover:text-white transition flex items-center gap-1.5 self-start md:self-auto">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span x-text="copiedEndpoint ? 'Tersalin!' : 'Salin URL'"></span>
                        </button>
                    </div>

                    <!-- Header Requirements -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 text-xs">
                        <div class="p-3 bg-blue-50 border border-blue-100 rounded-lg">
                            <p class="font-bold text-blue-900 mb-1">Header Otentikasi (Wajib):</p>
                            <code class="font-mono text-blue-800 block bg-white px-2 py-1 rounded border border-blue-200">X-API-KEY: <span x-text="selectedDeviceKey"></span></code>
                        </div>
                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                            <p class="font-bold text-gray-900 mb-1">Content-Type:</p>
                            <span class="font-mono text-gray-700 block bg-white px-2 py-1 rounded border border-gray-200">application/json <span class="text-gray-400">atau</span> multipart/form-data</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. PARAMETER REQUEST TABEL -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-bold text-base text-gray-800 flex items-center gap-2">
                        <span class="text-blue-600">📋</span> Parameter Permintaan (Request Body)
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-gray-100/75 border-b border-gray-200 text-gray-600 uppercase text-[11px] tracking-wider font-semibold">
                                <th class="py-3 px-6">Nama Parameter</th>
                                <th class="py-3 px-4">Tipe Data</th>
                                <th class="py-3 px-4">Wajib / Opsional</th>
                                <th class="py-3 px-6">Deskripsi &amp; Contoh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3.5 px-6 font-mono font-bold text-indigo-700">number</td>
                                <td class="py-3.5 px-4 font-mono text-gray-600">string</td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-red-100 text-red-700">Wajib</span>
                                </td>
                                <td class="py-3.5 px-6 text-gray-600 leading-relaxed">
                                    Nomor WhatsApp tujuan. Gunakan format internasional tanpa tanda <code class="bg-gray-100 px-1 rounded text-red-600">+</code> (contoh: <code class="font-mono font-bold text-gray-800">6281234567890</code>). Sistem otomatis menyesuaikan format JID WhatsApp.
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3.5 px-6 font-mono font-bold text-indigo-700">type</td>
                                <td class="py-3.5 px-4 font-mono text-gray-600">string</td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-red-100 text-red-700">Wajib</span>
                                </td>
                                <td class="py-3.5 px-6 text-gray-600 leading-relaxed">
                                    Jenis pesan yang ingin dikirimkan:
                                    <ul class="list-disc pl-4 mt-1 space-y-0.5">
                                        <li><code class="font-mono text-blue-700 font-semibold">'text'</code> : Mengirim pesan teks reguler.</li>
                                        <li><code class="font-mono text-blue-700 font-semibold">'file'</code> : Mengirim berkas dokumen atau gambar media.</li>
                                    </ul>
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3.5 px-6 font-mono font-bold text-indigo-700">message</td>
                                <td class="py-3.5 px-4 font-mono text-gray-600">string</td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-700">Opsional*</span>
                                </td>
                                <td class="py-3.5 px-6 text-gray-600 leading-relaxed">
                                    • Untuk <code class="font-mono">type: 'text'</code>: Isi teks pesan yang dikirim ke penerima.<br>
                                    • Untuk <code class="font-mono">type: 'file'</code>: Berfungsi sebagai takarir (<i>caption</i>) file/gambar.
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3.5 px-6 font-mono font-bold text-indigo-700">url</td>
                                <td class="py-3.5 px-4 font-mono text-gray-600">string (URL)</td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-700">Opsional*</span>
                                </td>
                                <td class="py-3.5 px-6 text-gray-600 leading-relaxed">
                                    Tautan URL publik berkas yang dapat diakses dari internet (contoh: <code class="font-mono text-xs">https://example.com/invoice.pdf</code>). Wajib diisi jika <code class="font-mono">type: 'file'</code> dan Anda tidak mengunggah file fisik.
                                </td>
                            </tr>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3.5 px-6 font-mono font-bold text-indigo-700">file</td>
                                <td class="py-3.5 px-4 font-mono text-gray-600">binary</td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-100 text-amber-700">Opsional*</span>
                                </td>
                                <td class="py-3.5 px-6 text-gray-600 leading-relaxed">
                                    File fisik langsung yang diunggah via form-data (Maksimal: <b class="text-gray-800">50 MB</b>). Mendukung format JPG, PNG, WEBP, PDF, DOCX, XLSX, ZIP, dll.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 4. CONTOH IMPLEMENTASI KODE (TAB INTERAKTIF) -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
                    <h3 class="font-bold text-base text-gray-800 flex items-center gap-2">
                        <span>💻</span> Contoh Kode Penggunaan
                    </h3>

                    <!-- Tombol Tab Bahasa -->
                    <div class="flex items-center gap-1.5 p-1 bg-gray-200 rounded-lg text-xs font-semibold">
                        <button type="button" @click="activeTab = 'curl'" :class="activeTab === 'curl' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="px-3 py-1.5 rounded-md transition">cURL</button>
                        <button type="button" @click="activeTab = 'php'" :class="activeTab === 'php' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="px-3 py-1.5 rounded-md transition">PHP</button>
                        <button type="button" @click="activeTab = 'laravel'" :class="activeTab === 'laravel' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="px-3 py-1.5 rounded-md transition">Laravel</button>
                        <button type="button" @click="activeTab = 'nodejs'" :class="activeTab === 'nodejs' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="px-3 py-1.5 rounded-md transition">Node.js</button>
                        <button type="button" @click="activeTab = 'python'" :class="activeTab === 'python' ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-600 hover:text-gray-900'" class="px-3 py-1.5 rounded-md transition">Python</button>
                    </div>
                </div>

                <div class="p-6">

                    <!-- TAB 1: cURL -->
                    <div x-show="activeTab === 'curl'" class="space-y-6">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-gray-700 uppercase tracking-wide">1. Kirim Pesan Teks (JSON):</span>
                                <button type="button" @click="copyText(document.getElementById('code-curl-text').innerText, 'key')" class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    Salin Snippet
                                </button>
                            </div>
                            <pre class="bg-gray-900 text-gray-100 rounded-xl p-4 text-xs font-mono overflow-x-auto leading-relaxed border border-gray-800"><code id="code-curl-text">curl -X POST "<span x-text="baseUrl"></span>/api/send" \
  -H "X-API-KEY: <span x-text="selectedDeviceKey"></span>" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "number": "6281234567890",
    "type": "text",
    "message": "Halo! Pesanan Anda telah dikonfirmasi. Terima kasih."
  }'</code></pre>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-gray-700 uppercase tracking-wide">2. Kirim Berkas via URL Online:</span>
                                <button type="button" @click="copyText(document.getElementById('code-curl-url').innerText, 'key')" class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    Salin Snippet
                                </button>
                            </div>
                            <pre class="bg-gray-900 text-gray-100 rounded-xl p-4 text-xs font-mono overflow-x-auto leading-relaxed border border-gray-800"><code id="code-curl-url">curl -X POST "<span x-text="baseUrl"></span>/api/send" \
  -H "X-API-KEY: <span x-text="selectedDeviceKey"></span>" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "number": "6281234567890",
    "type": "file",
    "url": "https://example.com/invoice-2026.pdf",
    "message": "Silakan unduh dokumen tagihan Anda terlampir."
  }'</code></pre>
                        </div>
                    </div>

                    <!-- TAB 2: PHP -->
                    <div x-show="activeTab === 'php'" class="space-y-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-gray-700 uppercase tracking-wide">PHP Native cURL:</span>
                            <button type="button" @click="copyText(document.getElementById('code-php').innerText, 'key')" class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Salin Snippet
                            </button>
                        </div>
                        <pre class="bg-gray-900 text-gray-100 rounded-xl p-4 text-xs font-mono overflow-x-auto leading-relaxed border border-gray-800"><code id="code-php">&lt;?php

$curl = curl_init();

$payload = [
    'number'  =&gt; '6281234567890',
    'type'    =&gt; 'text',
    'message' =&gt; "Halo! Kode OTP login Anda adalah: *78291*.\nJangan berikan kode ini kepada siapapun."
];

curl_setopt_array($curl, [
    CURLOPT_URL =&gt; "<span x-text="baseUrl"></span>/api/send",
    CURLOPT_RETURNTRANSFER =&gt; true,
    CURLOPT_POST =&gt; true,
    CURLOPT_POSTFIELDS =&gt; json_encode($payload),
    CURLOPT_HTTPHEADER =&gt; [
        "X-API-KEY: <span x-text="selectedDeviceKey"></span>",
        "Content-Type: application/json",
        "Accept: application/json"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo "cURL Error: " . $err;
} else {
    echo $response;
}</code></pre>
                    </div>

                    <!-- TAB 3: LARAVEL -->
                    <div x-show="activeTab === 'laravel'" class="space-y-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-gray-700 uppercase tracking-wide">Laravel HTTP Client (Guzzle):</span>
                            <button type="button" @click="copyText(document.getElementById('code-laravel').innerText, 'key')" class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Salin Snippet
                            </button>
                        </div>
                        <pre class="bg-gray-900 text-gray-100 rounded-xl p-4 text-xs font-mono overflow-x-auto leading-relaxed border border-gray-800"><code id="code-laravel">use Illuminate\Support\Facades\Http;

$response = Http::withHeaders([
    'X-API-KEY' =&gt; '<span x-text="selectedDeviceKey"></span>',
    'Accept'    =&gt; 'application/json',
])-&gt;post('<span x-text="baseUrl"></span>/api/send', [
    'number'  =&gt; '6281234567890',
    'type'    =&gt; 'text',
    'message' =&gt; 'Halo dari aplikasi Laravel!'
]);

if ($response-&gt;successful()) {
    $result = $response-&gt;json();
    // Berhasil dikirim via SIMAWA
} else {
    // Tangani error
    $error = $response-&gt;json('message');
}</code></pre>
                    </div>

                    <!-- TAB 4: NODE.JS -->
                    <div x-show="activeTab === 'nodejs'" class="space-y-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-gray-700 uppercase tracking-wide">Node.js (Axios):</span>
                            <button type="button" @click="copyText(document.getElementById('code-node').innerText, 'key')" class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Salin Snippet
                            </button>
                        </div>
                        <pre class="bg-gray-900 text-gray-100 rounded-xl p-4 text-xs font-mono overflow-x-auto leading-relaxed border border-gray-800"><code id="code-node">const axios = require('axios');

async function sendWhatsApp() {
  try {
    const response = await axios.post('<span x-text="baseUrl"></span>/api/send', {
      number: '6281234567890',
      type: 'text',
      message: 'Halo! Ini adalah notifikasi otomatis dari Node.js.'
    }, {
      headers: {
        'X-API-KEY': '<span x-text="selectedDeviceKey"></span>',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });

    console.log('Sukses:', response.data);
  } catch (error) {
    console.error('Error:', error.response ? error.response.data : error.message);
  }
}

sendWhatsApp();</code></pre>
                    </div>

                    <!-- TAB 5: PYTHON -->
                    <div x-show="activeTab === 'python'" class="space-y-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-gray-700 uppercase tracking-wide">Python (Requests):</span>
                            <button type="button" @click="copyText(document.getElementById('code-python').innerText, 'key')" class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Salin Snippet
                            </button>
                        </div>
                        <pre class="bg-gray-900 text-gray-100 rounded-xl p-4 text-xs font-mono overflow-x-auto leading-relaxed border border-gray-800"><code id="code-python">import requests

url = "<span x-text="baseUrl"></span>/api/send"

headers = {
    "X-API-KEY": "<span x-text="selectedDeviceKey"></span>",
    "Content-Type": "application/json",
    "Accept": "application/json"
}

payload = {
    "number": "6281234567890",
    "type": "text",
    "message": "Halo dari aplikasi Python!"
}

response = requests.post(url, json=payload, headers=headers)
print(response.status_code)
print(response.json())</code></pre>
                    </div>

                </div>
            </div>

            <!-- 5. FORMAT BALASAN (RESPONSES) -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-bold text-base text-gray-800 flex items-center gap-2">
                        <span>📥</span> Format Respon (Responses JSON)
                    </h3>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Respon Sukses -->
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800 font-mono">200 OK</span>
                            <span class="text-xs font-bold text-gray-700">Pesan Berhasil Terkirim:</span>
                        </div>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded-xl text-xs font-mono border border-gray-800 overflow-x-auto"><code>{
  "status": "success",
  "message": "Pesan/File berhasil dikirim via SIMAWA",
  "gowa_response": {
    "code": "SUCCESS",
    "message": "Success send message",
    "results": {
      "id": "3EB047284901920B2F",
      "timestamp": "2026-11-05T09:05:00Z"
    }
  }
}</code></pre>
                    </div>

                    <!-- Respon Error 401 -->
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800 font-mono">401 Unauthorized</span>
                            <span class="text-xs font-bold text-gray-700">API Key Tidak Valid / Hilang:</span>
                        </div>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded-xl text-xs font-mono border border-gray-800 overflow-x-auto"><code>{
  "status": "error",
  "message": "Akses ditolak! API Key tidak valid."
}</code></pre>
                    </div>

                    <!-- Respon Error 400 Device Offline -->
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800 font-mono">400 Bad Request</span>
                            <span class="text-xs font-bold text-gray-700">Device WhatsApp Terputus:</span>
                        </div>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded-xl text-xs font-mono border border-gray-800 overflow-x-auto"><code>{
  "status": "error",
  "message": "Device sedang terputus (disconnected)."
}</code></pre>
                    </div>

                    <!-- Respon Error 422 Validasi -->
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-purple-100 text-purple-800 font-mono">422 Unprocessable</span>
                            <span class="text-xs font-bold text-gray-700">Parameter Kurang / Salah Format:</span>
                        </div>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded-xl text-xs font-mono border border-gray-800 overflow-x-auto"><code>{
  "message": "The number field is required.",
  "errors": {
    "number": [
      "The number field is required."
    ]
  }
}</code></pre>
                    </div>
                </div>
            </div>

            <!-- 6. CATATAN & BEST PRACTICES -->
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 text-xs text-blue-900 space-y-3">
                <h4 class="font-bold text-sm text-blue-950 flex items-center gap-2">
                    <span>💡</span> Catatan Penting &amp; Fitur Otomatis SIMAWA:
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                    <div class="p-3 bg-white rounded-lg border border-blue-200">
                        <p class="font-bold text-blue-900 mb-0.5">💬 Sinkronisasi Live Chat Otomatis</p>
                        <p class="text-gray-600">Jika pengaturan <i>Live Chat</i> diaktifkan pada device, setiap pesan yang dikirim oleh aplikasi eksternal melalui REST API akan otomatis dicatat pada riwayat obrolan di menu <b>Live Chat</b>.</p>
                    </div>
                    <div class="p-3 bg-white rounded-lg border border-blue-200">
                        <p class="font-bold text-blue-900 mb-0.5">📱 Format Nomor Internasional Otomatis</p>
                        <p class="text-gray-600">Sistem otomatis mendeteksi dan menambahkan akhiran <code class="bg-gray-100 px-1 text-gray-800 font-mono">@s.whatsapp.net</code>. Anda cukup mengirimkan nomor polos seperti <code class="font-mono text-gray-800">6281234567890</code>.</p>
                    </div>
                    <div class="p-3 bg-white rounded-lg border border-blue-200">
                        <p class="font-bold text-blue-900 mb-0.5">🛡️ Keamanan Kunci Rahasia</p>
                        <p class="text-gray-600">API Key bersifat privat dan terikat pada masing-masing device. Jangan masukkan API Key ke dalam repository Git publik atau aplikasi klien sisi pengguna (front-end SPA tanpa backend proxy).</p>
                    </div>
                    <div class="p-3 bg-white rounded-lg border border-blue-200">
                        <p class="font-bold text-blue-900 mb-0.5">📦 Pengiriman Berkas Otomatis</p>
                        <p class="text-gray-600">Sistem otomatis mengenali tipe berkas: ekstensi gambar (<code class="font-mono text-gray-800">jpg, png, webp</code>) akan dikirim sebagai Gambar WhatsApp, sedangkan berkas lain dikirim sebagai Dokumen.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
