<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    private function getBackgroundColor($role)
    {
        $colors = [
            'admin' => '1a237e', // Azul oscuro
            'organizador' => '2e7d32', // Verde
            'participante' => 'c62828', // Rojo
            'default' => '424242' // Gris
        ];
        
        return $colors[$role] ?? $colors['default'];
    }

    public function definition()
    {
        $role = $this->faker->randomElement(['organizador', 'participante']);
        $nombre = $this->faker->firstName;
        $backgroundColor = $this->getBackgroundColor($role);
        
        return [
            'nombre' => $nombre,
            'apellido1' => $this->faker->lastName,
            'apellido2' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password123'), // Contraseña predeterminada
            'role' => $role,
            'remember_token' => Str::random(10),
            'avatar' => "https://ui-avatars.com/api/?name=" . urlencode($nombre) . "&background=" . $backgroundColor . "&color=fff&bold=true&format=png"
        ];
    }
}