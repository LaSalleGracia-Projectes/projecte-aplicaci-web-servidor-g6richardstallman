<?php

namespace Database\Factories;

use App\Models\Valoracion;
use App\Models\Clients;
use App\Models\Evento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Valoracion>
 */
class ValoracionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Valoracion::class;

public function definition()
    {
        return [
            'idCliente' => Clients::inRandomOrder()->first()->idCliente,
            'idEvento' => Evento::inRandomOrder()->first()->idEvento,
            'puntuacion' => $this->faker->numberBetween(1, 5),
            'comentario' => $this->faker->sentence(10)
        ];
    }
}