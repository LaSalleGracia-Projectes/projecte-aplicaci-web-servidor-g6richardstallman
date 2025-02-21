<?php

namespace Database\Factories;

use App\Models\Evento;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Evento>
 */
class EventoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Evento::class;

public function definition()
    {
        return [
            'idEmpresa' => Empresa::inRandomOrder()->first()->idEmpresa,
            'nombreEvento' => $this->faker->sentence(3),
            'fechaEvento' => $this->faker->dateTimeBetween('now', '+1 year'),
            'lugar' => $this->faker->address
        ];
    }
}