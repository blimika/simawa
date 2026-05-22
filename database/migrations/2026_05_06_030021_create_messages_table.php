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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            // user_id adalah siapa agen/admin yang mengetik pesan ini (bisa null jika pesan masuk dari pelanggan)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('number'); // Nomor tujuan / pengirim
            $table->text('message'); // Isi pesan
            $table->enum('direction', ['in', 'out']); // 'in' = pesan masuk, 'out' = pesan keluar
            $table->string('status')->default('sent'); // pending, sent, delivered, read, failed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
