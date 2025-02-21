<?php

namespace Database\Factories;

use App\Models\Estadistiques;
use App\Models\Empresa;
use App\Models\Evento;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class EstadistiquesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Estadistiques::class;


    public function definition()
    {
        return [
            'idEmpresa' => Empresa::inRandomOrder()->first()->idEmpresa,
            'idEvento' => Evento::inRandomOrder()->first()->idEvento,
            'visitesTotals' => $this->faker->numberBetween(0, 1000),
            'entradesVendes' => $this->faker->numberBetween(0, 1000),
            'tipusPlan' => Plan::inRandomOrder()->first()->idPlan,
            'valoracioProm' => $this->faker->randomFloat(2, 0, 5)
        ];
    }
}