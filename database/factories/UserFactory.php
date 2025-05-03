<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Organizador;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

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
            'avatar' => null,
        ];
    }

    public function participante(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'participante',
        ])->afterCreating(function (User $user) {
            $iniciales = strtoupper(substr($user->nombre, 0, 1) . substr($user->apellido1, 0, 1));
            $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($iniciales) . '&background=0D8ABC&color=fff&size=256';
            $user->update(['avatar' => $avatarUrl]);
            \App\Models\Participante::factory()->create(['idUser' => $user->idUser]);
        });
    }

    public function organizador(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'organizador',
        ])->afterCreating(function (User $user) {
            $organizador = Organizador::factory()->create(['user_id' => $user->idUser]);
            $iniciales = strtoupper(substr($organizador->nombre_organizacion, 0, 2));
            $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($iniciales) . '&background=0D8ABC&color=fff&size=256';
            $user->update(['avatar' => $avatarUrl]);
        });
    }
}