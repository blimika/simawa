<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            // user_id adalah Admin pusat yang mendaftarkan device ini
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Contoh: "CS Pusat", "CS Cabang Bali"
            $table->string('phone_number');
            $table->string('gowa_session_id')->unique();
            $table->enum('status', ['connected', 'disconnected', 'pending'])->default('pending');
            $table->string('webhook_url')->nullable(); // Untuk forward pesan ke sistem lain
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
