<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BroadcastRecipient extends Model
{
    protected $fillable = [
        'broadcast_campaign_id',
        'phonebook_id',
        'name',
        'number',
        'message',
        'status',
        'sent_at',
        'error_message',
        'response_data',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(BroadcastCampaign::class, 'broadcast_campaign_id');
    }

    public function phonebook(): BelongsTo
    {
        return $this->belongsTo(Phonebook::class);
    }
}
