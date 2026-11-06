<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BroadcastCampaign extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'name',
        'category',
        'message',
        'media_url',
        'delay_min',
        'delay_max',
        'scheduled_at',
        'status',
        'total_recipients',
        'sent_count',
        'failed_count',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'delay_min' => 'integer',
        'delay_max' => 'integer',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(BroadcastRecipient::class);
    }

    public function getPendingCountAttribute(): int
    {
        return max(0, $this->total_recipients - ($this->sent_count + $this->failed_count));
    }

    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_recipients <= 0) {
            return 0;
        }

        $processed = $this->sent_count + $this->failed_count;
        return (int) min(100, round(($processed / $this->total_recipients) * 100));
    }

    public function isCompleted(): bool
    {
        return in_array($this->status, ['completed', 'failed', 'cancelled']);
    }

    public function isRunning(): bool
    {
        return $this->status === 'processing';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }
}
