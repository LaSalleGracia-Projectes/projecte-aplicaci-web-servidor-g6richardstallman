<?php

namespace Database\Seeders;

use App\Models\Evento;
use App\Models\Organizador;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegurarse de que existen organizadores
        if (Organizador::count() == 0) {
            $this->call(OrganizadorSeeder::class);
        }
        
        $this->command->info('Creando eventos (limitado a 5)...');

        // Crear solo 5 eventos específicos
        $eventos = [
            [
                'nombreEvento' => 'Festival de Música Electrónica',
                'fechaEvento' => '2025-12-15',
                'descripcion' => 'El mejor festival de música electrónica con DJs internacionales.',
                'horaEvento' => '22:00:00',
                'aforo' => 5000,
                'precioMinimo' => 50.00,
                'precioMaximo' => 150.00,
                'ubicacion' => 'Santiago Bernabeu, Madrid',
                'imagen' => 'eventos/default.jpg',
                'categoria' => 'Festival',
                'lugar' => 'Santiago Bernabeu',
                'idOrganizador' => Organizador::inRandomOrder()->first()->idOrganizador,
            ],
            [
                'nombreEvento' => 'Concierto de Rock Clásico',
                'fechaEvento' => '2025-11-25',
                'descripcion' => 'Revive los mejores éxitos del rock de los 80 y 90.',
                'horaEvento' => '20:30:00',
                'aforo' => 8000,
                'precioMinimo' => 40.00,
                'precioMaximo' => 120.00,
                'ubicacion' => 'Camp Nou, Barcelona',
                'imagen' => 'eventos/default.jpg',
                'categoria' => 'Concierto',
                'lugar' => 'Camp Nou, Barcelona',
                'idOrganizador' => Organizador::inRandomOrder()->first()->idOrganizador,
            ],
            [
                'nombreEvento' => 'Obra de Teatro: Romeo y Julieta',
                'fechaEvento' => '2025-12-05',
                'descripcion' => 'Clásica obra de Shakespeare interpretada por la compañía nacional.',
                'horaEvento' => '19:00:00',
                'aforo' => 500,
                'precioMinimo' => 25.00,
                'precioMaximo' => 75.00,
                'ubicacion' => 'Mestalla, Valencia',
                'imagen' => 'eventos/default.jpg',
                'categoria' => 'Teatro',
                'lugar' => 'Mestalla Valencia',
                'idOrganizador' => Organizador::inRandomOrder()->first()->idOrganizador,
            ],
            [
                'nombreEvento' => 'Conferencia de Tecnología',
                'fechaEvento' => '2025-01-20',
                'descripcion' => 'Expertos en IA y blockchain comparten las últimas tendencias.',
                'horaEvento' => '10:00:00',
                'aforo' => 1000,
                'precioMinimo' => 100.00,
                'precioMaximo' => 300.00,
                'ubicacion' => 'La Cartuja, Sevilla',
                'imagen' => 'eventos/default.jpg',
                'categoria' => 'Conferencia',
                'lugar' => 'La Cartuja Sevilla',
                'idOrganizador' => Organizador::inRandomOrder()->first()->idOrganizador,
            ],
            [
                'nombreEvento' => 'Exposición de Arte Contemporáneo',
                'fechaEvento' => '2025-02-10',
                'descripcion' => 'Obras de los artistas más innovadores del momento.',
                'horaEvento' => '11:00:00',
                'aforo' => 300,
                'precioMinimo' => 10.00,
                'precioMaximo' => 25.00,
                'ubicacion' => 'Estadio San Mames, Bilbao',
                'imagen' => 'eventos/default.jpg',
                'categoria' => 'Exposición',
                'lugar' => 'Estadio San Mames, Bilbao',
                'idOrganizador' => Organizador::inRandomOrder()->first()->idOrganizador,
            ],
        ];

        foreach ($eventos as $evento) {
            Evento::create($evento);
        }
        
        // No crear eventos aleatorios adicionales
        // Evento::factory()->count(15)->create();
        
        // Asegurarse de que todos los eventos tengan tipos de entrada
        $this->call(TipoEntradaSeeder::class);
        
        $this->command->info('Eventos creados con éxito');
    }
}