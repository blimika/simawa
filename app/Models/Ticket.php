<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number', 'device_id', 'number', 'customer_name', 'agent_id', 'status', 'tag', 'has_new_message'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
