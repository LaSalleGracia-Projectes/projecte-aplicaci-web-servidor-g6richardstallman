<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->firstName(),
            'apellido1' => fake()->lastName(),
            'apellido2' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => fake()->randomElement(['participante', 'organizador']),
            'remember_token' => Str::random(10),
        ];
    }

    public function participante(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'participante',
        ]);
    }

    public function organizador(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'organizador',
        ]);
    }
}