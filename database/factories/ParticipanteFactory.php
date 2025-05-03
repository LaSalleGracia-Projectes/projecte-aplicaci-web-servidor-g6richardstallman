<?php

namespace Database\Factories;

use App\Models\Participante;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipanteFactory extends Factory
{
    protected $model = Participante::class;

    public function definition()
    {
        return [
            'dni' => fake()->unique()->regexify('[0-9]{8}[A-Z]'),
            'telefono' => fake()->numerify('#########'), // 9 dígitos
            'direccion' => fake()->address(), // Añadir dirección
            // Se asume que idUser será proporcionado al llamar a create()
        ];
    }
}