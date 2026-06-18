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
        Schema::table('devices', function (Blueprint $table) {
            // Default (true/1) = Live chat nyala. (false/0) = Hanya webhook saja
            $table->boolean('is_livechat_enabled')->default(true)->after('webhook_url');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('is_livechat_enabled');
        });
    }
};
