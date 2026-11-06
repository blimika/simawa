<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'user_uid',
        'is_active',
        'parent_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean', // Pastikan di-cast ke boolean
        ];
    }
    // Fungsi otomatis untuk generate user_uid sebelum data disimpan ke database
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->user_uid)) {
                // Menghasilkan string acak 6 karakter (huruf dan angka), diubah ke huruf besar
                $user->user_uid = strtoupper(Str::random(6));
            }
        });
    }
    // Relasi untuk Admin: 1 Admin bisa mendaftarkan banyak Device
    public function ownedDevices()
    {
        return $this->hasMany(Device::class, 'user_id');
    }

    // Relasi untuk Agent: 1 Agent bisa di-assign ke banyak Device (Pivot)
    public function assignedDevices()
    {
        return $this->belongsToMany(Device::class, 'agent_device', 'user_id', 'device_id')->withTimestamps();
    }

    // Relasi: Device yang ditugaskan kepada Agen ini (Many-to-Many)
    public function handledDevices()
    {
        return $this->belongsToMany(Device::class, 'agent_device', 'user_id', 'device_id')->withTimestamps();
    }
    // Relasi: Mendapatkan Admin dari seorang Agent
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    // Relasi: Mendapatkan daftar Agent di bawah seorang Admin
    public function subordinates()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    // Helper untuk cek Role
    public function isSuperadmin() { return $this->role === 'superadmin'; }
    public function isAdmin() { return $this->role === 'admin'; }
    public function isAgent() { return $this->role === 'agent'; }

    /**
     * Helper untuk mendapatkan "Owner ID"
     * Jika dia Agent, maka owner-nya adalah parent_id (Admin)
     * Jika dia Admin, owner-nya adalah dirinya sendiri
     */
    public function getOwnerId()
    {
        return $this->isAgent() ? $this->parent_id : $this->id;
    }
    // Relasi: Device yang dimiliki oleh Admin (One-to-Many)
    public function devices()
    {
        return $this->hasMany(Device::class, 'user_id');
    }
}
