<?php

namespace Database\Factories;

use App\Models\Valoracion;
use App\Models\Evento;
use App\Models\Participante;
use Illuminate\Database\Eloquent\Factories\Factory;

class ValoracionFactory extends Factory
{
    protected $model = Valoracion::class;

    public function definition()
    {
        return [
            'puntuacion' => $this->faker->numberBetween(1, 5),
            'comentario' => $this->faker->sentence,
            'idEvento' => Evento::factory(),
            'idParticipante' => Participante::factory()
        ];
    }
}