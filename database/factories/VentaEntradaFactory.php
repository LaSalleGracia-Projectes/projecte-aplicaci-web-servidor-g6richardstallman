<?php

namespace Database\Factories;


use App\Models\VentaEntrada;
use App\Models\Clients;
use App\Models\Entrada;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VentaEntrada>
 */
class VentaEntradaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = VentaEntrada::class;

    public function definition(): array
    {
        return [
            'dniCliente' => Clients::inRandomOrder()->first()->dni,
            'fechaCompra' => $this->faker->dateTimeThisYear,
            'estadoPago' => $this->faker->randomElement(['Pagat', 'Pendent', 'Cancel·lat']),
            'subtotal' => $this->faker->randomFloat(2, 20, 500),
            'impuestos' => $this->faker->randomFloat(2, 5, 50),
            'descuento' => $this->faker->randomFloat(2, 0, 30),
            'montoTotal' => $this->faker->randomFloat(2, 20, 500),
            'idEntrada' => Entrada::inRandomOrder()->first()->idEntrada,
            'idPago' => Pago::inRandomOrder()->first()->idPago
        ];
    }
}