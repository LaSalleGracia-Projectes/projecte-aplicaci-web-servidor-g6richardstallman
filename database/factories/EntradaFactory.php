<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Entrada;
use App\Models\Evento;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Entrada>
 */
class EntradaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Entrada::class;

    public function definition()
    {
        return [
            'fechaVenta' => $this->faker->dateTimeThisYear,
            'nombrePersona' => $this->faker->firstName,
            'apellido1' => $this->faker->lastName,
            'apellido2' => $this->faker->lastName,
            'idEvento' => Evento::inRandomOrder()->first()->idEvento
        ];
    }
}