<?php

namespace Database\Factories;

use App\Models\VentaEntrada;
use App\Models\Entrada;
use App\Models\Pago;
use App\Models\Participante;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class VentaEntradaFactory extends Factory
{
    protected $model = VentaEntrada::class;

    public function definition()
    {
        $precio = $this->faker->randomFloat(2, 20, 100);
        $ivaRate = 0.21; // Asumir IVA 21%
        $impuestos = round($precio * $ivaRate, 2); // Calcular impuesto sobre el precio
        // Asegurar que existen Entrada, Pago, Participante
        $entrada = Entrada::inRandomOrder()->first() ?? Entrada::factory()->create();
        $pago = Pago::inRandomOrder()->first() ?? Pago::factory()->create();
        $participante = Participante::inRandomOrder()->first() ?? Participante::factory()->create();

        return [
            'estado_pago' => $this->faker->randomElement(['Pagado', 'Pendiente']),
            'precio' => $precio,
            'impuestos' => $impuestos,
            'fecha_compra' => Carbon::now()->subDays(rand(1, 30)),
            'idEntrada' => $entrada->idEntrada,
            'idPago' => $pago->idPago,
            'idParticipante' => $participante->idParticipante
        ];
    }
    
    // Estado específico para venta con pago pendiente
    public function pendiente()
    {
        return $this->state(function (array $attributes) {
            return [
                'estado_pago' => 'Pendiente',
            ];
        });
    }
    
    // Estado específico para venta pagada
    public function pagado()
    {
        return $this->state(function (array $attributes) {
            return [
                'estado_pago' => 'Pagado',
            ];
        });
    }
}