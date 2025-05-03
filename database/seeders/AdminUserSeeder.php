<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $nombre = 'Admin';
        $apellido1 = 'Admin';
        $iniciales = strtoupper(substr($nombre, 0, 1) . substr($apellido1, 0, 1));
        $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($iniciales) . '&background=0D8ABC&color=fff&size=256';

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@admin.com'], // Clave para buscar o insertar
            [
                'nombre' => $nombre,
                'apellido1' => $apellido1,
                'apellido2' => 'Admin',
                'password' => Hash::make('Adminadmin1.'),
                'role' => 'admin',
                'avatar' => $avatarUrl,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
} 