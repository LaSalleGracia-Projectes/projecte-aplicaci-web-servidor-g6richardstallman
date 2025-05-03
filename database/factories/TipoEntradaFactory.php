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
        $tiposEntrada = ['General', 'VIP', 'Premium', 'Grada', 'Pista']; // Nombres más comunes
        $esIlimitado = fake()->boolean(10); // 10% probabilidad
        $evento = Evento::inRandomOrder()->first() ?? Evento::factory()->create(); // Asegurar evento
        
        return [
            'idEvento' => $evento->idEvento,
            'nombre' => fake()->randomElement($tiposEntrada),
            'precio' => fake()->randomFloat(2, 10, 150), // Precios más ajustados
            'cantidad_disponible' => $esIlimitado ? null : fake()->numberBetween(50, 500),
            'entradas_vendidas' => 0,
            'descripcion' => fake()->optional(0.8)->sentence(), // Descripción más corta y opcional
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
                'precio' => fake()->randomFloat(2, 80, 300), // Ajustar precio VIP
                'descripcion' => 'Entrada VIP con acceso preferente y consumición.',
                'es_ilimitado' => false,
                'cantidad_disponible' => fake()->numberBetween(20, 80)
            ];
        });
    }

    // Estado para entradas online ilimitadas
    public function online()
    {
        return $this->state(function (array $attributes) {
            return [
                'nombre' => 'Streaming Online',
                'precio' => $this->faker->randomFloat(2, 5, 15),
                'descripcion' => 'Acceso ilimitado para ver el evento en streaming',
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
                'nombre' => 'Early Bird',
                'precio' => $this->faker->randomFloat(2, 10, 30),
                'descripcion' => 'Entradas anticipadas a precio reducido (AGOTADAS)',
                'es_ilimitado' => false,
                'cantidad_disponible' => $cantidad,
                'entradas_vendidas' => $cantidad
            ];
        });
    }
    
    // Estado para entradas General
    public function general()
    {
        return $this->state(function (array $attributes) {
            return [
                'nombre' => 'General',
                'precio' => $this->faker->randomFloat(2, 15, 50),
                'descripcion' => 'Entrada estándar para el evento',
                'es_ilimitado' => false,
                'cantidad_disponible' => $this->faker->numberBetween(100, 500)
            ];
        });
    }

    // Estado para entradas Premium
    public function premium()
    {
        return $this->state(function (array $attributes) {
            return [
                'nombre' => 'Premium',
                'precio' => $this->faker->randomFloat(2, 60, 120),
                'descripcion' => 'Entrada premium con acceso a zona preferente',
                'es_ilimitado' => false,
                'cantidad_disponible' => $this->faker->numberBetween(50, 200)
            ];
        });
    }

    /**
     * Crear conjunto completo de tipos de entrada para un evento
     * 
     * @param int $idEvento ID del evento
     * @return array Array de TipoEntrada creados
     */
    public function crearTiposParaEvento($idEvento)
    {
        $tipos = [];
        
        // Número de tipos de entrada: entre 2 y 4
        $tiposCount = rand(2, 4);
        
        // Siempre crear entrada General
        $tipos[] = $this->general()->create(['idEvento' => $idEvento]);
        
        // Siempre crear entrada VIP
        $tipos[] = $this->vip()->create(['idEvento' => $idEvento]);
        
        if ($tiposCount >= 3) {
            // Crear entrada Premium
            $tipos[] = $this->premium()->create(['idEvento' => $idEvento]);
        }
        
        if ($tiposCount >= 4) {
            // 50% posibilidad de crear entrada online o agotada
            if ($this->faker->boolean(50)) {
                $tipos[] = $this->online()->create(['idEvento' => $idEvento]);
            } else {
                $tipos[] = $this->agotado()->create(['idEvento' => $idEvento]);
            }
        }
        
        return $tipos;
    }
} 