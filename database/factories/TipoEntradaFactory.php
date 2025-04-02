<?php

namespace Database\Factories;

use App\Models\TipoEntrada;
use App\Models\Evento;
use Illuminate\Database\Eloquent\Factories\Factory;

class TipoEntradaFactory extends Factory
{
    protected $model = TipoEntrada::class;

    public function definition()
    {
        $tiposEntrada = ['General', 'VIP', 'Premium', 'Early Bird', 'Meet & Greet'];
        $esIlimitado = $this->faker->boolean(20); // 20% de probabilidad de ser ilimitado
        
        return [
            'idEvento' => Evento::factory(),
            'nombre' => $this->faker->randomElement($tiposEntrada),
            'precio' => $this->faker->randomFloat(2, 10, 300),
            'cantidad_disponible' => $esIlimitado ? null : $this->faker->numberBetween(50, 1000),
            'entradas_vendidas' => 0,
            'descripcion' => $this->faker->paragraph(),
            'es_ilimitado' => $esIlimitado,
            'activo' => true
        ];
    }

    // Estado para entradas VIP
    public function vip()
    {
        return $this->state(function (array $attributes) {
            return [
                'nombre' => 'VIP',
                'precio' => $this->faker->randomFloat(2, 100, 500),
                'descripcion' => 'Entrada VIP con acceso a todas las áreas y beneficios exclusivos',
                'es_ilimitado' => false,
                'cantidad_disponible' => $this->faker->numberBetween(20, 100)
            ];
        });
    }

    // Estado para entradas online ilimitadas
    public function online()
    {
        return $this->state(function (array $attributes) {
            return [
                'es_ilimitado' => true,
                'cantidad_disponible' => null
            ];
        });
    }

    // Estado para entradas agotadas
    public function agotado()
    {
        return $this->state(function (array $attributes) {
            $cantidad = $this->faker->numberBetween(50, 1000);
            return [
                'es_ilimitado' => false,
                'cantidad_disponible' => $cantidad,
                'entradas_vendidas' => $cantidad
            ];
        });
    }
} 