<?php

namespace Database\Factories;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empresa>
 */
class EmpresaFactory extends Factory
{
    protected $model = Empresa::class;

public function definition()
    {
        return [
            'nombre' => $this->faker->company,
            'contacto' => $this->faker->name,
            'telefono' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->companyEmail,
            'tipoPlan' => $this->faker->numberBetween(1, 3)
        ];
    }
}