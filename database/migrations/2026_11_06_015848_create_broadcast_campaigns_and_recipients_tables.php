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
        Schema::create('broadcast_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->default('all');
            $table->text('message');
            $table->string('media_url')->nullable();
            $table->unsignedInteger('delay_min')->default(5);
            $table->unsignedInteger('delay_max')->default(10);
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status')->default('pending'); // pending, scheduled, processing, completed, failed, paused, cancelled
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('broadcast_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broadcast_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('phonebook_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name')->default('Tanpa Nama');
            $table->string('number');
            $table->text('message');
            $table->string('status')->default('pending'); // pending, processing, sent, failed
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->text('response_data')->nullable();
            $table->timestamps();

            $table->index(['broadcast_campaign_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broadcast_recipients');
        Schema::dropIfExists('broadcast_campaigns');
    }
};
