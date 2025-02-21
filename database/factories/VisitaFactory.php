<?php

namespace Database\Factories;

use App\Models\Visita;
use App\Models\Clients;
use App\Models\Empresa;
use App\Models\Evento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Visita>
 */
class VisitaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Visita::class;

    public function definition()
    {
        return [
            'idClient' => Clients::inRandomOrder()->first()->idCliente,
            'idEmpresa' => Empresa::inRandomOrder()->first()->idEmpresa,
            'idEvent' => Evento::inRandomOrder()->first()->idEvento,
            'fechaVisita' => $this->faker->dateTimeThisYear,
            'tipoVisita' => $this->faker->randomElement(['SiFavorito', 'NoFavorito'])
        ];
    }
}