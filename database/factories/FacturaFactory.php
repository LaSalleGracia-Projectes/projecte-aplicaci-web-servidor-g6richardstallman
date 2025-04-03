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
        // Verificar que existan participantes, entradas y pagos
        $this->crearDatosRequeridos();
        
        // Obtener elementos aleatorios para asociar
        $participante = Participante::inRandomOrder()->first();
        $entrada = Entrada::inRandomOrder()->first();
        $pago = Pago::inRandomOrder()->first();
        
        // Obtener información de evento y tipo de entrada si es posible
        $evento = null;
        $tipoEntrada = null;
        $precio = $this->faker->randomFloat(2, 50, 200);
        
        if ($entrada && $entrada->idEvento) {
            $evento = Evento::find($entrada->idEvento);
            if ($evento) {
                $tipoEntrada = TipoEntrada::where('idEvento', $evento->idEvento)->first();
                if ($tipoEntrada) {
                    $precio = $tipoEntrada->precio;
                }
            }
        }
        
        // Calcular importes
        $subtotal = round($precio / (1 + VentaEntrada::IVA), 2);
        $impuestos = round($precio - $subtotal, 2);
        $total = $precio;
        
        // Generar fechas
        $fechaEmision = $this->faker->dateTimeBetween('-1 year', 'now');
        $fechaVencimiento = Carbon::parse($fechaEmision)->addDays(30);
        
        // Generar número de factura único con UUID para evitar colisiones
        $anio = Carbon::parse($fechaEmision)->format('Y');
        $uuid = Str::uuid()->toString();
        $numeroFactura = $anio . '-' . substr($uuid, 0, 5);
        
        return [
            'numero_factura' => $numeroFactura,
            'fecha_emision' => $fechaEmision,
            'fecha_vencimiento' => $fechaVencimiento,
            'subtotal' => $subtotal,
            'impostos' => $impuestos,
            'descuento' => 0, // Sin descuento por defecto
            'montoTotal' => $total,
            'estado' => $this->faker->randomElement(['emitida', 'pagada', 'cancelada']),
            'nombre_fiscal' => $this->faker->company,
            'nif' => $this->faker->regexify('[0-9]{8}[A-Z]{1}'),
            'direccion_fiscal' => $this->faker->address,
            'metodo_pago' => $this->faker->randomElement(['tarjeta', 'transferencia', 'paypal']),
            'notas' => $this->faker->optional(0.7)->sentence(),
            'idParticipante' => $participante ? $participante->idParticipante : Participante::factory(),
            'idEntrada' => $entrada ? $entrada->idEntrada : Entrada::factory(),
            'idPago' => $pago ? $pago->idPago : Pago::factory()
        ];
    }
    
    /**
     * Crear datos requeridos si no existen
     */
    private function crearDatosRequeridos()
    {
        // Verificar y crear participantes si es necesario
        if (Participante::count() == 0) {
            Participante::factory(5)->create();
        }
        
        // Verificar y crear entradas si es necesario
        if (Entrada::count() == 0) {
            // Crear eventos con sus tipos de entrada si es necesario
            if (Evento::count() == 0) {
                Evento::factory(3)->create();
            }
            
            $eventos = Evento::all();
            
            foreach ($eventos as $evento) {
                // Crear tipos de entrada para el evento si es necesario
                if (TipoEntrada::where('idEvento', $evento->idEvento)->count() == 0) {
                    TipoEntrada::factory(2)->create([
                        'idEvento' => $evento->idEvento
                    ]);
                }
                
                // Crear entradas para este evento
                for ($i = 0; $i < 3; $i++) {
                    Entrada::factory()->create([
                        'idEvento' => $evento->idEvento,
                        'fecha_venta' => now()->subDays(rand(1, 30))
                    ]);
                }
            }
        }
        
        // Verificar y crear pagos si es necesario
        if (Pago::count() == 0) {
            Pago::factory(5)->create();
        }
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