<?php

namespace Database\Factories;

use App\Models\Organizador;
use App\Models\Participante;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizadorFavoritoFactory extends Factory
{
    // protected $model = App\Models\OrganizadorFavorito::class; // Especificar modelo si existe

    public function definition()
    {
        // Asegurar que existen participantes y organizadores
        $participante = Participante::inRandomOrder()->first() ?? Participante::factory()->create();
        $organizador = Organizador::inRandomOrder()->first() ?? Organizador::factory()->create(); // CUIDADO: OrganizadorFactory ahora no crea User

        return [
            'idParticipante' => $participante->idParticipante,
            'idOrganizador' => $organizador->idOrganizador
        ];
    }
} 