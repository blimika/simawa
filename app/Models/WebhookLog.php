<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    // Sesuaikan dengan schema Anda
    protected $fillable = ['device_id', 'sender_number', 'message', 'payload'];

    // Opsional: Relasi ke tabel devices
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
