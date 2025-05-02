<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'nombre' => 'Admin',
            'apellido1' => 'Admin',
            'apellido2' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('Adminadmin1.'),
            'role' => 'admin',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
} 