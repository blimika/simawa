<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController; // <- INI WAJIB ADA
use App\Http\Controllers\DeviceController;    // <- INI JUGA WAJIB ADA
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\PhonebookController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Grup Route yang wajib Login dan Akun Aktif
Route::middleware(['auth', 'active.user'])->group(function () {

    // Halaman Dashboard Utama
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Route bawaan Breeze untuk Edit Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // ==========================================
    // AREA KHUSUS ADMIN
    // ==========================================
    /*
    Route::middleware([\App\Http\Middleware\IsAdmin::class])->group(function () {

        // Rute-rute Custom Device (Letakkan DI ATAS Route::resource)
        Route::get('/devices/{device}/scan', [DeviceController::class, 'scan'])->name('devices.scan');
        Route::post('/devices/{device}/request-qr', [DeviceController::class, 'requestQr'])->name('devices.request_qr');

        // ---> INI BARIS YANG MEMBUAT ERROR TADI (Pastikan ada) <---
        Route::get('/devices/{device}/status-json', [DeviceController::class, 'statusJson'])->name('devices.status_json');

        Route::post('/devices/{device}/check-status', [DeviceController::class, 'checkStatus'])->name('devices.check_status');
        Route::post('/devices/{device}/reconnect', [DeviceController::class, 'reconnect'])->name('devices.reconnect');
        Route::post('/devices/{device}/logout', [DeviceController::class, 'logout'])->name('devices.logout');
        Route::post('/devices/{device}/passkey-confirm', [DeviceController::class, 'passkeyConfirm'])->name('devices.passkey_confirm');

        // Rute Default CRUD Device
        Route::resource('devices', DeviceController::class);

        // (Nanti kita tambahkan route untuk manajemen Agen di sini)
    });
    */
    // --- KHUSUS SUPERADMIN ---
    Route::middleware(['role:superadmin'])->group(function () {
        Route::resource('admins', AdminController::class)->except(['show']);
        Route::post('devices/sync', [App\Http\Controllers\DeviceController::class, 'syncDevices'])->name('devices.sync');
    });
    // --- AKSES SUPERADMIN & ADMIN ---
    Route::middleware(['role:superadmin,admin'])->group(function () {        // Manage Devices
        Route::get('devices/{device}/edit', [App\Http\Controllers\DeviceController::class, 'edit'])->name('devices.edit');
        Route::put('devices/{device}', [App\Http\Controllers\DeviceController::class, 'update'])->name('devices.update');
        Route::get('/devices/{device}/scan', [DeviceController::class, 'scan'])->name('devices.scan');
        Route::post('/devices/{device}/request-qr', [DeviceController::class, 'requestQr'])->name('devices.request_qr');
        Route::post('/devices/{device}/request-pairing-code', [DeviceController::class, 'requestPairingCode'])->name('devices.request_pairing_code');
        Route::get('/devices/{device}/status-json', [DeviceController::class, 'statusJson'])->name('devices.status_json');
        Route::post('/devices/{device}/check-status', [DeviceController::class, 'checkStatus'])->name('devices.check_status');
        Route::post('/devices/{device}/reconnect', [DeviceController::class, 'reconnect'])->name('devices.reconnect');
        Route::post('/devices/{device}/logout', [DeviceController::class, 'logout'])->name('devices.logout');
        Route::post('/devices/{device}/passkey-confirm', [DeviceController::class, 'passkeyConfirm'])->name('devices.passkey_confirm');
        Route::post('/devices/{device}/passkey-response', [DeviceController::class, 'passkeyResponse'])->name('devices.passkey_response');
        Route::resource('devices', DeviceController::class);
        // Rute untuk Manajemen Massal Agen
        Route::get('/agents/template', [App\Http\Controllers\AgentController::class, 'downloadTemplate'])->name('agents.template');
        Route::post('/agents/import', [App\Http\Controllers\AgentController::class, 'import'])->name('agents.import');
        Route::post('/agents/bulk-delete', [App\Http\Controllers\AgentController::class, 'bulkDelete'])->name('agents.bulk-delete');

        // (Pastikan ini ada di bawah rute-rute di atas)
        // Route::resource('agents', AgentController::class);
        // Manage Agents
        Route::resource('agents', AgentController::class);

        // Halaman Dokumentasi API (Hanya Admin & Superadmin)
        Route::get('/api-docs', [App\Http\Controllers\ApiDocController::class, 'index'])->name('api-docs.index');
    });

    // --- AKSES SEMUA (Superadmin, Admin, Agent) ---
    Route::middleware(['role:superadmin,admin,agent'])->group(function () {
        // Phonebook & Messages
        // Rute untuk Download Template CSV (Taruh di atas Route::resource)
        Route::get('/phonebook/template', [PhonebookController::class, 'downloadTemplate'])->name('phonebook.template');
        Route::post('/phonebook/import', [PhonebookController::class, 'import'])->name('phonebook.import');
        Route::post('/phonebook/bulk-delete', [PhonebookController::class, 'bulkDelete'])->name('phonebook.bulk-delete');
        Route::put('phonebook/{id}', [App\Http\Controllers\PhonebookController::class, 'update'])->name('phonebook.update');
        // Rute Resource otomatis untuk index, create, store, edit, update, destroy
        Route::resource('phonebook', PhonebookController::class);
        // Rute BARU untuk Transfer / Ambil Alih Tiket
        Route::post('messages/{ticket}/transfer', [MessageController::class, 'transferTicket'])->name('messages.transfer');
        Route::post('messages/{ticket}/close', [MessageController::class, 'closeTicket'])->name('messages.close');
        Route::post('messages/{ticket}/tag', [App\Http\Controllers\MessageController::class, 'updateTag'])->name('messages.update_tag');
        Route::post('messages/{ticket}/start', [App\Http\Controllers\MessageController::class, 'startService'])->name('messages.start');
        Route::resource('messages', MessageController::class)->only(['index', 'store']);
        // Fitur Broadcast
        Route::get('broadcast', [BroadcastController::class, 'create'])->name('broadcast.create');
        Route::post('broadcast', [BroadcastController::class, 'store'])->name('broadcast.store');

        Route::get('/webhook-logs', [WebhookController::class, 'index'])->name('webhook-logs.index');
        Route::post('/webhook-logs/clear', [WebhookController::class, 'clear'])->name('webhook-logs.clear');
    });
});

require __DIR__.'/auth.php';
