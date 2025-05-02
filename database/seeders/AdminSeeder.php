<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'nombre' => 'Adminadmin',
            'apellido1' => 'System',
            'apellido2' => null,
            'email' => 'admin@admin.com',
            'password' => Hash::make('Adminadmin1'),
            'role' => 'admin'
        ]);
    }
} 