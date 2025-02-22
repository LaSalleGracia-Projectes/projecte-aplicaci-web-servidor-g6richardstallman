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
            'dni' => $this->faker->unique()->regexify('[0-9]{8}[A-Z]'),
            'telefono' => $this->faker->phoneNumber,
            'user_id' => User::factory()->create(['role' => 'participante'])->id
        ];
    }
}