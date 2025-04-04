<?php

namespace Database\Seeders;

use App\Models\Entrada;
use App\Models\TipoEntrada;
use App\Models\Evento;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EntradaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar que existan tipos de entrada
        $tiposEntrada = TipoEntrada::all();
        
        if ($tiposEntrada->isEmpty()) {
            $this->command->info('No hay tipos de entrada. Ejecutando TipoEntradaSeeder...');
            $this->call(TipoEntradaSeeder::class);
            $tiposEntrada = TipoEntrada::all();
        }
        
        $this->command->info('Creando entradas físicas para los tipos de entrada (máximo 10 por tipo)...');
        
        foreach ($tiposEntrada as $tipo) {
            // Saltamos los tipos ilimitados o agotados
            if ($tipo->es_ilimitado || $tipo->entradas_vendidas >= $tipo->cantidad_disponible) {
                continue;
            }
            
            // Crear un número limitado de entradas (máximo 10 por tipo)
            $cantidadACrear = min(10, $tipo->cantidad_disponible - $tipo->entradas_vendidas);
            
            for ($i = 0; $i < $cantidadACrear; $i++) {
                // Generar un código único usando microtime y uniqid para garantizar unicidad
                $codigo = 'ENT-' . $tipo->idEvento . '-' . $tipo->idTipoEntrada . '-' . uniqid(microtime(), true);
                
                // Verificar si existe algún código igual en la base de datos
                while (Entrada::where('codigo', $codigo)->exists()) {
                    $codigo = 'ENT-' . $tipo->idEvento . '-' . $tipo->idTipoEntrada . '-' . uniqid(microtime(), true);
                }
                
                Entrada::create([
                    'idEvento' => $tipo->idEvento,
                    'idTipoEntrada' => $tipo->idTipoEntrada,
                    'codigo' => $codigo,
                    'estado' => 'disponible',
                    'precio' => $tipo->precio
                ]);
            }
            
            $this->command->info("Creadas {$cantidadACrear} entradas para el tipo '{$tipo->nombre}' del evento ID {$tipo->idEvento}");
        }
        
        // Crear algunas entradas vendidas (máximo 5)
        $entradasDisponibles = Entrada::where('estado', 'disponible')->take(5)->get();
        $cantidad = $entradasDisponibles->count();
        
        for ($i = 0; $i < $cantidad; $i++) {
            $entrada = $entradasDisponibles[$i];
            $entrada->update([
                'estado' => 'vendida',
                'fecha_venta' => Carbon::now()->subDays(rand(1, 10))
            ]);
        }
        
        $this->command->info("Marcadas {$cantidad} entradas como vendidas");
        $this->command->info('Entradas físicas creadas con éxito');
    }
}