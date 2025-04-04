<?php

namespace Database\Seeders;

use App\Models\Entrada;
use App\Models\Participante;
use App\Models\TipoEntrada;
use App\Models\VentaEntrada;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class VentaEntradaSeeder extends Seeder
{
    public function run()
    {
        // Inicializar Faker
        $faker = Faker::create();
        
        // Comprobar si existen participantes y entradas
        $participantes = Participante::all();
        $entradas = Entrada::where('estado', 'disponible')->take(10)->get();
        
        if ($participantes->isEmpty() || $entradas->isEmpty()) {
            echo "No hay suficientes participantes o entradas para crear ventas.\n";
            return;
        }
        
        $this->command->info('Creando ventas de entradas (máximo 10)...');
        
        // Limitar a 10 ventas como máximo
        $count = min(10, $entradas->count());
        
        for ($i = 0; $i < $count; $i++) {
            // Seleccionar una entrada disponible al azar
            $entrada = $entradas[$i];
            
            // Seleccionar un participante al azar
            $participante = $participantes->random();
            
            // Obtener el tipo de entrada
            $tipoEntrada = TipoEntrada::find($entrada->idTipoEntrada);
            
            // Crear la venta
            $venta = new VentaEntrada();
            $venta->idEntrada = $entrada->idEntrada;
            $venta->idParticipante = $participante->idParticipante;
            $venta->fecha_compra = now();
            $venta->estado_pago = $faker->randomElement(['Pagado', 'Pendiente']);
            
            // Utilizar el método del modelo para calcular precio e impuestos
            $venta->setPrecioAndCalculateValues($tipoEntrada->precio);
            $venta->save();
            
            // Actualizar el estado de la entrada
            $entrada->estado = 'vendida';
            $entrada->save();
            
            // Corregir la sintaxis aquí - usar paréntesis fuera de las llaves
            $numeroVenta = $i + 1;
            $this->command->info("Venta {$numeroVenta} creada: Entrada {$entrada->idEntrada} para Participante {$participante->idParticipante}");
        }
        
        $this->command->info('Ventas de entradas creadas con éxito.');
    }
}