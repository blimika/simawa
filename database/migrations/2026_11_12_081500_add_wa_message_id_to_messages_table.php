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
        Schema::table('messages', function (Blueprint $table) {
            $table->string('wa_message_id')->nullable()->after('quoted_message')->index();
            $table->unsignedBigInteger('reply_to_id')->nullable()->after('wa_message_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['wa_message_id']);
            $table->dropIndex(['reply_to_id']);
            $table->dropColumn(['wa_message_id', 'reply_to_id']);
        });
    }
};
