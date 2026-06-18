<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'user_id', 'name', 'phone_number', 'gowa_session_id', 'status', 'webhook_url', 'is_livechat_enabled'
    ];

    // Tambahkan baris ini agar selalu dibaca sebagai true/false
    protected $casts = [
        'is_livechat_enabled' => 'boolean',
    ];
    // Admin pemilik device ini
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Agent yang ditugaskan memegang device ini
    public function agents()
    {
        return $this->belongsToMany(User::class, 'agent_device', 'device_id', 'user_id')->withTimestamps();
    }

    // Relasi ke histori chat
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
    // Di dalam Model Device
    public function scopeForUser($query)
    {
        $user = auth()->user();
        if ($user->role === 'superadmin') return $query;

        if ($user->role === 'admin') {
            return $query->where('user_id', $user->id);
        }

        if ($user->role === 'agent') {
            // Agent melihat device milik Admin-nya
            return $query->where('user_id', $user->parent_id);
        }
    }
}
