<?php

namespace Database\Factories;

use App\Models\TipoEntrada;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipoEntradaFactory extends Factory
{
    protected $model = TipoEntrada::class;

    public function definition()
    {
        $categorias = [
            'VIP' => [
                'Palco VIP',
                'Zona Premium',
                'Meet & Greet'
            ],
            'General' => [
                'Entrada General',
                'Pista',
                'Campo'
            ],
            'Tribuna' => [
                'Tribuna Norte',
                'Tribuna Sur',
                'Tribuna Este',
                'Tribuna Oeste'
            ],
            'Especial' => [
                'Early Bird',
                'Estudiante',
                'Jubilado'
            ]
        ];

        $categoria = $this->faker->randomElement(array_keys($categorias));
        $nombre = $this->faker->randomElement($categorias[$categoria]);

        return [
            'nombre' => $nombre,
            'descripcion' => $this->faker->sentence(),
            'categoria' => $categoria
        ];
    }
} 