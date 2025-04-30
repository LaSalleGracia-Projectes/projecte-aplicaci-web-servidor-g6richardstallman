<?php

namespace Database\Seeders;

use App\Models\Favorito;
use App\Models\Participante;
use App\Models\Evento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FavoritoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si las tablas existen
        if (!Schema::hasTable('participante') || !Schema::hasTable('evento')) {
            $this->command->info('Las tablas necesarias no existen. Ejecutando migraciones...');
            $this->call([
                DatabaseSeeder::class
            ]);
            return;
        }

        // Verificar si hay participantes y eventos
        $participantes = Participante::all();
        $eventos = Evento::all();

        if ($participantes->isEmpty()) {
            $this->command->info('No hay participantes. Ejecutando ParticipanteSeeder...');
            $this->call(ParticipanteSeeder::class);
            $participantes = Participante::all();
        }
        
        if ($eventos->isEmpty()) {
            $this->command->info('No hay eventos. Ejecutando EventoSeeder...');
            $this->call(EventoSeeder::class);
            $eventos = Evento::all();
        }
        
        // Para cada participante, marcar algunos eventos como favoritos
        foreach ($participantes as $participante) {
            // Seleccionar aleatoriamente entre 1 y 5 eventos para marcar como favoritos
            $numEventos = min(5, $eventos->count());
            if ($numEventos > 0) {
                $eventosAleatorios = $eventos->random(rand(1, $numEventos));
            
            foreach ($eventosAleatorios as $evento) {
                // Verificar si ya existe este favorito para evitar duplicados
                $existeFavorito = Favorito::where('idParticipante', $participante->idParticipante)
                                         ->where('idEvento', $evento->idEvento)
                                         ->exists();
                
                if (!$existeFavorito) {
                    Favorito::create([
                        'idParticipante' => $participante->idParticipante,
                        'idEvento' => $evento->idEvento,
                        'fechaAgregado' => now()->subDays(rand(0, 30))
                    ]);
                }
            }
        }
        }

        $this->command->info('Favoritos creados exitosamente.');
    }
} 