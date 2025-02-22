<?php

namespace Database\Factories;

use App\Models\VentaEntrada;
use App\Models\Entrada;
use App\Models\Pago;
use App\Models\Participante;
use Illuminate\Database\Eloquent\Factories\Factory;

class VentaEntradaFactory extends Factory
{
    protected $model = VentaEntrada::class;

    public function definition()
    {
        $subtotal = $this->faker->randomFloat(2, 20, 100);
        $impuestos = $subtotal * 0.21;
        $descuento = $this->faker->randomFloat(2, 0, $subtotal * 0.2);
        $montoTotal = $subtotal + $impuestos - $descuento;

        return [
            'estado_pago' => $this->faker->randomElement(['Pagado', 'Pendiente']),
            'subtotal' => $subtotal,
            'impuestos' => $impuestos,
            'descuento' => $descuento,
            'monto_total' => $montoTotal,
            'idEntrada' => Entrada::factory(),
            'idPago' => Pago::factory(),
            'idParticipante' => Participante::factory()
        ];
    }
}