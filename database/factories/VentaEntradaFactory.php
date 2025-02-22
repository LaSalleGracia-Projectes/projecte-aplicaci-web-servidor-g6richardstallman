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
        $subtotal = $this->faker->randomFloat(2, 10, 100);
        $impuestos = $subtotal * 0.21;
        $descuento = $this->faker->randomFloat(2, 0, $subtotal * 0.2);
        $monto_total = $subtotal + $impuestos - $descuento;

        return [
            'estado_pago' => $this->faker->randomElement(['Pagado', 'Pendiente', 'Cancelado']),
            'subtotal' => $subtotal,
            'impuestos' => $impuestos,
            'descuento' => $descuento,
            'monto_total' => $monto_total,
            'entrada_id' => Entrada::factory(),
            'pago_id' => Pago::factory(),
            'participante_id' => Participante::factory()
        ];
    }
}