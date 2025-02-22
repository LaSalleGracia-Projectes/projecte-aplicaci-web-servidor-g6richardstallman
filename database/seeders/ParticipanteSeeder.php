<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Participante;

class ParticipanteSeeder extends Seeder
{
    public function run(): void
    {
        Participante::factory(10)->create();
    }
}