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
        Schema::table('users', function (Blueprint $table) {
        // Mengubah role menjadi enum agar lebih ketat (Superadmin, Admin, Agent)
        // Kita gunakan change() jika kolom role sudah ada, atau buat baru jika belum.
        $table->enum('role', ['superadmin', 'admin', 'agent'])->default('agent')->change();

        // Menambahkan parent_id untuk hierarki
        // Admin & Superadmin memiliki parent_id NULL
        // Agent memiliki parent_id yang merujuk ke ID Admin-nya
        $table->foreignId('parent_id')->nullable()
              ->after('id')
              ->constrained('users')
              ->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['parent_id']);
        $table->dropColumn('parent_id');
        // Kembalikan role ke tipe string biasa jika perlu
        $table->string('role')->default('agent')->change();
    });
    }
};
