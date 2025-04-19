<?php

namespace Database\Factories;

use App\Models\Evento;
use App\Models\Organizador;
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
        // Obtener un organizador aleatorio o crear uno si no existe
        $organizador = Organizador::inRandomOrder()->first() ?? Organizador::factory()->create();
        
        // Categorías de eventos
        $categorias = [
            'Cultura',
            'Deporte',
            'Otra',
            'Concierto', 
            'Festival', 
            'Teatro', 
            'Cine', 
            'Deportes', 
            'Conferencia', 
            'Exposición',
            'Taller',
            'Networking',
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
            'descripcion' => $this->faker->paragraph(),
            'hora' => $this->faker->time('H:i:s'),
            'ubicacion' => $this->faker->address(),
            'imagen' => 'eventos/default.jpg',
            'categoria' => $this->faker->randomElement($categorias),
            'lugar' => $this->faker->randomElement($lugares) . ' ' . $this->faker->city,
            'idOrganizador' => $organizador->idOrganizador,
        ];
    }
}