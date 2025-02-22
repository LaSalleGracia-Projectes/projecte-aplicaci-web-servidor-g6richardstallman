<?php

namespace Database\Factories;

use App\Models\Evento;
use App\Models\Organizador;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventoFactory extends Factory
{
    protected $model = Evento::class;

    public function definition()
    {
        return [
            'nombreEvento' => $this->faker->sentence(3),
            'fechaEvento' => $this->faker->dateTimeBetween('now', '+1 year'),
            'lugar' => $this->faker->city,
            'idOrganizador' => Organizador::factory()->create()->idOrganizador
        ];
    }
}