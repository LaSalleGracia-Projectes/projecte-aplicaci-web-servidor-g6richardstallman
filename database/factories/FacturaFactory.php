<?php

namespace Database\Factories;

use App\Models\Factura;
use App\Models\Participante;
use App\Models\Entrada;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacturaFactory extends Factory
{
    protected $model = Factura::class;

    public function definition()
    {
        $subtotal = $this->faker->randomFloat(2, 50, 200);
        $impostos = $subtotal * 0.21;
        $descuento = $this->faker->randomFloat(2, 0, $subtotal * 0.2);
        $montoTotal = $subtotal + $impostos - $descuento;

        return [
            'montoTotal' => $montoTotal,
            'descuento' => $descuento,
            'impostos' => $impostos,
            'subtotal' => $subtotal,
            'idParticipante' => Participante::factory(),
            'idEntrada' => Entrada::factory(),
            'idPago' => Pago::factory()
        ];
    }
}