<?php

namespace Database\Factories;

use App\Models\Organizador;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizadorFactory extends Factory
{
    protected $model = Organizador::class;

    public function definition()
    {
        return [
            'nombre_organizacion' => $this->faker->company,
            'telefono_contacto' => $this->faker->phoneNumber,
            'user_id' => User::factory()->create()->idUser
        ];
    }
}