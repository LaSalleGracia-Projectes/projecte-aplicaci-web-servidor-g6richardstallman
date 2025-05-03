<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear 5 organizadores
        User::factory()
            ->count(5)
            ->organizador()
            ->create();

        // Crear 5 participantes
        User::factory()
            ->count(5)
            ->participante()
            ->create();
    }
}