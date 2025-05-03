<?php

namespace Database\Factories;

use App\Models\Favorito;
use App\Models\Participante;
use App\Models\Evento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Favorito>
 */
class FavoritoFactory extends Factory
{
    protected $model = Favorito::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Asegurar que existen participantes y eventos
        $participante = Participante::inRandomOrder()->first() ?? Participante::factory()->create();
        $evento = Evento::inRandomOrder()->first() ?? Evento::factory()->create();
        
        return [
            'idParticipante' => $participante->idParticipante,
            'idEvento' => $evento->idEvento,
            // 'fechaAgregado' se asigna por defecto en la migración con useCurrent()
        ];
    }
} 