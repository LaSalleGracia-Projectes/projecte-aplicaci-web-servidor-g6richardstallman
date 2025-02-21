<?php

namespace Database\Factories;

use App\Models\Pago;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pago>
 */
class PagoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Pago::class;
    public function definition()
    {
        return [
            'nombre' => $this->faker->name,
            'contacto' => $this->faker->name,
            'telefono' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'tipoPlan' => Plan::inRandomOrder()->first()->idPlan
        ];
    }
}