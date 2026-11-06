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
            $table->text('greeting_message')->nullable()->after('is_livechat_enabled');
            $table->text('claim_message')->nullable()->after('greeting_message');
            $table->text('closing_message')->nullable()->after('claim_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['greeting_message', 'claim_message', 'closing_message']);
        });
    }
};
