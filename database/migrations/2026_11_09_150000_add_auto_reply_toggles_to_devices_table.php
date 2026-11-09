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
            $table->boolean('is_greeting_enabled')->default(true)->after('closing_message');
            $table->boolean('is_claim_enabled')->default(true)->after('is_greeting_enabled');
            $table->boolean('is_closing_enabled')->default(true)->after('is_claim_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['is_greeting_enabled', 'is_claim_enabled', 'is_closing_enabled']);
        });
    }
};
