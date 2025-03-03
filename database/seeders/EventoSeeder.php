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

        // Crear eventos con datos específicos
        $eventos = [
            [
                'nombreEvento' => 'Festival de Música Electrónica',
                'fechaEvento' => '2023-12-15',
                'descripcion' => 'El mejor festival de música electrónica con DJs internacionales.',
                'hora' => '22:00:00',
                'ubicacion' => 'Recinto Ferial, Madrid',
                'imagen' => 'eventos/festival.jpg',
                'categoria' => 'Festival',
                'lugar' => 'Recinto Ferial Madrid',
                'idOrganizador' => Organizador::inRandomOrder()->first()->idOrganizador,
            ],
            [
                'nombreEvento' => 'Concierto de Rock Clásico',
                'fechaEvento' => '2023-11-25',
                'descripcion' => 'Revive los mejores éxitos del rock de los 80 y 90.',
                'hora' => '20:30:00',
                'ubicacion' => 'Palacio de Deportes, Barcelona',
                'imagen' => 'eventos/concierto.jpg',
                'categoria' => 'Concierto',
                'lugar' => 'Palacio de Deportes Barcelona',
                'idOrganizador' => Organizador::inRandomOrder()->first()->idOrganizador,
            ],
            [
                'nombreEvento' => 'Obra de Teatro: Romeo y Julieta',
                'fechaEvento' => '2023-12-05',
                'descripcion' => 'Clásica obra de Shakespeare interpretada por la compañía nacional.',
                'hora' => '19:00:00',
                'ubicacion' => 'Teatro Principal, Valencia',
                'imagen' => 'eventos/teatro.jpg',
                'categoria' => 'Teatro',
                'lugar' => 'Teatro Principal Valencia',
                'idOrganizador' => Organizador::inRandomOrder()->first()->idOrganizador,
            ],
            [
                'nombreEvento' => 'Conferencia de Tecnología',
                'fechaEvento' => '2024-01-20',
                'descripcion' => 'Expertos en IA y blockchain comparten las últimas tendencias.',
                'hora' => '10:00:00',
                'ubicacion' => 'Centro de Convenciones, Sevilla',
                'imagen' => 'eventos/conferencia.jpg',
                'categoria' => 'Conferencia',
                'lugar' => 'Centro de Convenciones Sevilla',
                'idOrganizador' => Organizador::inRandomOrder()->first()->idOrganizador,
            ],
            [
                'nombreEvento' => 'Exposición de Arte Contemporáneo',
                'fechaEvento' => '2024-02-10',
                'descripcion' => 'Obras de los artistas más innovadores del momento.',
                'hora' => '11:00:00',
                'ubicacion' => 'Museo de Arte Moderno, Bilbao',
                'imagen' => 'eventos/exposicion.jpg',
                'categoria' => 'Exposición',
                'lugar' => 'Museo de Arte Moderno Bilbao',
                'idOrganizador' => Organizador::inRandomOrder()->first()->idOrganizador,
            ],
        ];

        foreach ($eventos as $evento) {
            Evento::create($evento);
        }

        // Crear eventos adicionales aleatorios
        Evento::factory()->count(15)->create();
    }
}