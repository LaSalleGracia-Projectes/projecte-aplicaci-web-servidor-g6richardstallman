<?php

namespace Database\Factories;

use App\Models\Factura;
use App\Models\Participante;
use App\Models\Entrada;
use App\Models\Pago;
use App\Models\Evento;
use App\Models\TipoEntrada;
use App\Models\VentaEntrada;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class FacturaFactory extends Factory
{
    protected $model = Factura::class;

    public function definition()
    {
        // Asegurar datos requeridos
        $participante = Participante::inRandomOrder()->first() ?? Participante::factory()->create();
        $entrada = Entrada::inRandomOrder()->first() ?? Entrada::factory()->create();
        $pago = Pago::factory()->create();
        
        // Calcular importes (simplificado, adaptar si es necesario)
        $precio = $this->faker->randomFloat(2, 50, 200);
        $ivaRate = 0.21; // Asumir IVA 21%
        $subtotal = round($precio / (1 + $ivaRate), 2);
        $impuestos = round($precio - $subtotal, 2);
        $total = $precio;
        
        $fechaEmision = $this->faker->dateTimeBetween('-1 year', 'now');
        $fechaVencimiento = Carbon::parse($fechaEmision)->addDays(30);
        $numeroFactura = Carbon::parse($fechaEmision)->format('Y') . '-' . strtoupper(Str::random(6));

        return [
            'numero_factura' => $numeroFactura,
            'fecha_emision' => $fechaEmision,
            'fecha_vencimiento' => $fechaVencimiento,
            'subtotal' => $subtotal,
            'impostos' => $impuestos,
            'descuento' => 0,
            'montoTotal' => $total,
            'estado' => $this->faker->randomElement(['emitida', 'pagada', 'cancelada']),
            'nombre_fiscal' => $this->faker->company,
            'nif' => $this->faker->regexify('[0-9]{8}[A-Z]{1}'),
            'direccion_fiscal' => $this->faker->address,
            'metodo_pago' => $this->faker->randomElement(['tarjeta', 'transferencia', 'paypal']),
            'notas' => $this->faker->optional(0.7)->sentence(),
            'idParticipante' => $participante->idParticipante,
            'idEntrada' => $entrada->idEntrada,
            'idPago' => $pago->idPago
        ];
    }
    
    /**
     * Estado de factura pagada
     */
    public function pagada()
    {
        return $this->state(function (array $attributes) {
            return [
                'estado' => 'pagada',
            ];
        });
    }
    
    /**
     * Estado de factura emitida
     */
    public function emitida()
    {
        return $this->state(function (array $attributes) {
            return [
                'estado' => 'emitida',
            ];
        });
    }
    
    /**
     * Estado de factura cancelada
     */
    public function cancelada()
    {
        return $this->state(function (array $attributes) {
            return [
                'estado' => 'cancelada',
            ];
        });
    }
    
    /**
     * Factura con descuento
     */
    public function conDescuento($porcentaje = 10)
    {
        return $this->state(function (array $attributes) use ($porcentaje) {
            $subtotal = $attributes['subtotal'];
            $descuento = round($subtotal * ($porcentaje / 100), 2);
            $nuevoSubtotal = $subtotal - $descuento;
            $impuestos = round($nuevoSubtotal * VentaEntrada::IVA, 2);
            $total = $nuevoSubtotal + $impuestos;
            
            return [
                'descuento' => $descuento,
                'subtotal' => $nuevoSubtotal,
                'impostos' => $impuestos,
                'montoTotal' => $total,
            ];
        });
    }
}