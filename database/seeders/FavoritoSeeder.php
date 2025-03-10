<?php

namespace Database\Seeders;

use App\Models\Favorito;
use App\Models\Participante;
use App\Models\Evento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavoritoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Asegurarse de que existen participantes y eventos
        if (Participante::count() == 0) {
            $this->call(ParticipanteSeeder::class);
        }
        
        if (Evento::count() == 0) {
            $this->call(EventoSeeder::class);
        }
        
        // Obtener todos los participantes y eventos
        $participantes = Participante::all();
        $eventos = Evento::all();
        
        // Para cada participante, marcar algunos eventos como favoritos
        foreach ($participantes as $participante) {
            // Seleccionar aleatoriamente entre 1 y 5 eventos para marcar como favoritos
            $eventosAleatorios = $eventos->random(rand(1, min(5, $eventos->count())));
            
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
} 