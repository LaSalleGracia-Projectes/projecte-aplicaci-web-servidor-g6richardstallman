<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Participante;
use App\Models\Organizador;
use Illuminate\Support\Facades\DB;

class OrganizadorFavoritoSeeder extends Seeder
{
    public function run()
    {
        // Verificar si existen participantes y organizadores
        $participantes = Participante::all();
        $organizadores = Organizador::all();
        
        if ($participantes->isEmpty() || $organizadores->isEmpty()) {
            $this->command->info('No hay participantes u organizadores para crear favoritos.');
            return;
        }
        
        // Limpiar la tabla
        DB::table('organizador_favorito')->truncate();
        
        // Crear algunos favoritos aleatorios
        foreach ($participantes as $participante) {
            // Cada participante marca como favorito entre 0 y 3 organizadores
            $numFavoritos = rand(0, min(3, $organizadores->count()));
            
            if ($numFavoritos > 0) {
                $organizadoresAleatorios = $organizadores->random($numFavoritos);
                
                foreach ($organizadoresAleatorios as $organizador) {
                    $participante->organizadoresFavoritos()->attach($organizador->idOrganizador);
                    $this->command->info("Participante {$participante->idParticipante} marcó como favorito al organizador {$organizador->idOrganizador}");
                }
            }
        }
    }
} 