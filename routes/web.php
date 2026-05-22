<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController; // <- INI WAJIB ADA
use App\Http\Controllers\DeviceController;    // <- INI JUGA WAJIB ADA
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\PhonebookController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\BroadcastController;
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

        // Rute Default CRUD Device
        Route::resource('devices', DeviceController::class);

        // (Nanti kita tambahkan route untuk manajemen Agen di sini)
    });
    */
    // --- KHUSUS SUPERADMIN ---
    Route::middleware(['role:superadmin'])->group(function () {
        Route::resource('admins', AdminController::class)->except(['show']);
    });
    // --- AKSES SUPERADMIN & ADMIN ---
    Route::middleware(['role:superadmin,admin'])->group(function () {        // Manage Devices
        Route::get('/devices/{device}/scan', [DeviceController::class, 'scan'])->name('devices.scan');
        Route::post('/devices/{device}/request-qr', [DeviceController::class, 'requestQr'])->name('devices.request_qr');
        Route::get('/devices/{device}/status-json', [DeviceController::class, 'statusJson'])->name('devices.status_json');
        Route::post('/devices/{device}/check-status', [DeviceController::class, 'checkStatus'])->name('devices.check_status');
        Route::post('/devices/{device}/reconnect', [DeviceController::class, 'reconnect'])->name('devices.reconnect');
        Route::resource('devices', DeviceController::class);

        // Manage Agents
        Route::resource('agents', AgentController::class);
    });

    // --- AKSES SEMUA (Superadmin, Admin, Agent) ---
    Route::middleware(['role:superadmin,admin,agent'])->group(function () {
        // Phonebook & Messages
        Route::resource('phonebook', App\Http\Controllers\PhonebookController::class)->except(['show', 'edit', 'update']);
        Route::post('messages/{ticket}/close', [MessageController::class, 'closeTicket'])->name('messages.close');
        Route::resource('messages', MessageController::class)->only(['index', 'store']);
        // Fitur Broadcast
        Route::get('broadcast', [BroadcastController::class, 'create'])->name('broadcast.create');
        Route::post('broadcast', [BroadcastController::class, 'store'])->name('broadcast.store');
    });
});

require __DIR__.'/auth.php';
