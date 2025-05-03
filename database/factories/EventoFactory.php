<?php

namespace Database\Factories;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Evento>
 */
class EventoFactory extends Factory
{
    protected $model = Evento::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Obtener un ID de usuario organizador
        $organizador = User::where('role', 'organizador')->inRandomOrder()->first();
        if (!$organizador) {
            $organizador = User::factory()->organizador()->create();
        }
        
        // Categorías de eventos
        $categorias = [
            'Cultura',
            'Deporte',
            'Concierto',
            'Festival',
            'Teatro',
            'Cine',
            'Conferencia',
            'Exposición',
            'Taller',
            'Gastronomía'
        ];

        // Lugares comunes para eventos
        $lugares = [
            'Estadio', 
            'Teatro', 
            'Sala de conciertos', 
            'Centro de convenciones', 
            'Parque', 
            'Plaza', 
            'Auditorio',
            'Museo',
            'Centro cultural',
            'Hotel'
        ];

        return [
            'nombreEvento' => $this->faker->sentence(3),
            'fechaEvento' => $this->faker->dateTimeBetween('+1 week', '+1 year')->format('Y-m-d'),
            'horaEvento' => $this->faker->time('H:i:s'),
            'descripcion' => $this->faker->paragraph(),
            'aforo' => $this->faker->numberBetween(100, 10000),
            'precioMinimo' => $this->faker->randomFloat(2, 10, 50),
            'precioMaximo' => $this->faker->randomFloat(2, 50, 500),
            'ubicacion' => $this->faker->address(),
            'imagen' => 'eventos/default.jpg',
            'categoria' => $this->faker->randomElement($categorias),
            'lugar' => $this->faker->randomElement($lugares) . ' ' . $this->faker->city,
            'idOrganizador' => $organizador->idUser,
        ];
    }
}