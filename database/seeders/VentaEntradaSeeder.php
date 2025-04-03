<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VentaEntrada;
use App\Models\Entrada;
use App\Models\Participante;
use App\Models\TipoEntrada;

class VentaEntradaSeeder extends Seeder
{
    public function run()
    {
        // Comprobar si existen participantes y entradas
        $participantes = Participante::all();
        $entradas = Entrada::all();
        
        // Si no hay suficientes datos, no crear ventas
        if ($participantes->isEmpty() || $entradas->isEmpty()) {
            echo "No hay suficientes participantes o entradas para crear ventas.\n";
            return;
        }
        
        // Crear ventas aleatorias
        foreach($entradas->random(min(20, $entradas->count())) as $entrada) {
            $participante = $participantes->random();
            $tipoEntrada = TipoEntrada::find($entrada->idTipoEntrada);
            
            if (!$tipoEntrada) continue;
            
            $venta = new VentaEntrada();
            $venta->idEntrada = $entrada->idEntrada;
            $venta->idParticipante = $participante->idParticipante;
            $venta->fecha_compra = now();
            $venta->estado_pago = $this->faker->randomElement(['Pagado', 'Pendiente']);
            
            // Utilizar el método del modelo para calcular precio e impuestos
            $venta->setPrecioAndCalculateValues($tipoEntrada->precio);
            $venta->save();
        }
    }
}