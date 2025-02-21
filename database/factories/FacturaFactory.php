<?php

namespace Database\Factories;

use App\Models\Factura;
use App\Models\Clients;
use App\Models\Entrada;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Factura>
 */
class FacturaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Factura::class;

    public function definition()
    {
        return [
            'idCliente' => Clients::inRandomOrder()->first()->idCliente,
            'montoTotal' => $this->faker->randomFloat(2, 50, 1000),
            'descuento' => $this->faker->randomFloat(2, 0, 100),
            'impostos' => $this->faker->randomFloat(2, 5, 100),
            'subtotal' => $this->faker->randomFloat(2, 50, 1000),
            'idEntrada' => Entrada::inRandomOrder()->first()->idEntrada,
            'idPago' => Pago::inRandomOrder()->first()->idPago
        ];
    }
}