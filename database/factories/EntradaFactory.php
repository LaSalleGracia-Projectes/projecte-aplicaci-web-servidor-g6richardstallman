<?php

namespace Database\Factories;

use App\Models\Entrada;
use App\Models\Evento;
use Illuminate\Database\Eloquent\Factories\Factory;

class EntradaFactory extends Factory
{
    protected $model = Entrada::class;

    public function definition()
    {
        return [
            'fecha_venta' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'nombre_persona' => $this->faker->name,
            'idEvento' => Evento::factory()->create()->idEvento
        ];
    }
}