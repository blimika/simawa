<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MasterUser extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'     => 'Super Admin',
            'username' => 'super',
            'email'    => 'super@simawa.test',
            'password' => Hash::make('1'),
            'role'     => 'superadmin',
            // user_uid tidak perlu diisi manual karena sudah ditangani oleh function boot() di Model
        ]);
    }
}
