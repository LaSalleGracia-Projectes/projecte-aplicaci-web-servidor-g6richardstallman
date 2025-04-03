<?php

namespace Database\Factories;

use App\Models\Organizador;
use App\Models\Participante;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizadorFavoritoFactory extends Factory
{
    public function definition()
    {
        return [
            'idParticipante' => Participante::inRandomOrder()->first()->idParticipante ?? Participante::factory(),
            'idOrganizador' => Organizador::inRandomOrder()->first()->idOrganizador ?? Organizador::factory()
        ];
    }
} 