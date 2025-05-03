<?php

namespace Database\Factories;

use App\Models\Pago;
use Illuminate\Database\Eloquent\Factories\Factory;

class PagoFactory extends Factory
{
    protected $model = Pago::class;

    public function definition()
    {
        // Generar solo los campos que existen en la migración de la tabla 'pago'
        return [
            'nombre' => $this->faker->name,
            'contacto' => $this->faker->name,
            'telefono' => $this->faker->numerify('#########'), // Ajustado para 9 dígitos
            'email' => $this->faker->unique()->safeEmail
        ];
    }
}