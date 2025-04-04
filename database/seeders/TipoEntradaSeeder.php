<?php

namespace Database\Seeders;

use App\Models\Evento;
use App\Models\TipoEntrada;
use Illuminate\Database\Seeder;

class TipoEntradaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar que existan eventos
        $eventos = Evento::take(10)->get(); // limitar a 10 eventos
        
        if ($eventos->isEmpty()) {
            $this->command->info('No hay eventos. Ejecutando EventoSeeder...');
            $this->call(EventoSeeder::class);
            $eventos = Evento::take(10)->get();
        }
        
        $this->command->info('Creando tipos de entrada para eventos (máximo 10 eventos)...');
        
        foreach ($eventos as $evento) {
            // Verificar si el evento ya tiene tipos de entrada
            if ($evento->tiposEntrada()->count() > 0) {
                $this->command->info("El evento {$evento->nombreEvento} ya tiene tipos de entrada. Saltando...");
                continue;
            }
            
            // Cada evento tendrá máximo 2 tipos de entrada
            $tiposCount = min(2, rand(1, 2));
            
            // Siempre crear una entrada General
            TipoEntrada::factory()->create([
                'idEvento' => $evento->idEvento,
                'nombre' => 'General',
                'precio' => rand(15, 50),
                'descripcion' => 'Entrada estándar para el evento',
                'es_ilimitado' => false,
                'cantidad_disponible' => 20
            ]);
            
            if ($tiposCount >= 2) {
                // Crear entrada VIP
                TipoEntrada::factory()->vip()->create([
                    'idEvento' => $evento->idEvento,
                    'cantidad_disponible' => 10
                ]);
            }
            
            $this->command->info("Creados {$tiposCount} tipos de entrada para el evento '{$evento->nombreEvento}'");
        }
        
        $this->command->info('Tipos de entrada creados con éxito');
    }
} 