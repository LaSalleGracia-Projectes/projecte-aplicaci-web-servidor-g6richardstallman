<?php

namespace Database\Factories;

use App\Models\Pago;
use Illuminate\Database\Eloquent\Factories\Factory;

class PagoFactory extends Factory
{
    protected $model = Pago::class;

    public function definition()
    {
        return [
            'nombre' => $this->faker->name,
            'contacto' => $this->faker->name,
            'telefono' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail
        ];
    }
}