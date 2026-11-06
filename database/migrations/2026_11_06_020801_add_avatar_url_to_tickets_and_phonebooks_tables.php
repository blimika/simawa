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
        Schema::table('tickets', function (Blueprint $table) {
            $table->text('avatar_url')->nullable()->after('customer_name');
        });

        Schema::table('phonebooks', function (Blueprint $table) {
            $table->text('avatar_url')->nullable()->after('number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('avatar_url');
        });

        Schema::table('phonebooks', function (Blueprint $table) {
            $table->dropColumn('avatar_url');
        });
    }
};
