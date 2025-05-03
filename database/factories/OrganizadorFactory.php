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
        // IMPORTANTE: Este factory asume que user_id se proporcionará externamente
        // (por ejemplo, desde UserFactory o un seeder)
        // No crea un User aquí para evitar duplicados o inconsistencias.
        return [
            'nombre_organizacion' => fake()->company(),
            'telefono_contacto' => fake()->numerify('#########'), // 9 dígitos
            'direccion_fiscal' => fake()->address(),
            'cif' => strtoupper(fake()->bothify('?#######?'))
            // 'user_id' => User::factory()->create(['role' => 'organizador'])->idUser // NO HACER ESTO AQUÍ
        ];
    }
}