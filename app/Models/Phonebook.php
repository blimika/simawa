<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phonebook extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'number','category'];

    // Relasi ke User (Admin pembuat/pemilik kontak)
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
