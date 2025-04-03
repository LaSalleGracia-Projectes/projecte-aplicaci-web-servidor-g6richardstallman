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
        
        // Calculamos los impuestos usando la constante IVA definida en el modelo
        $impuestos = round($precio * VentaEntrada::IVA, 2);

        return [
            'estado_pago' => $this->faker->randomElement(['Pagado', 'Pendiente']),
            'precio' => $precio,
            'impuestos' => $impuestos,
            'fecha_compra' => Carbon::now()->subDays(rand(1, 30)),
            'idEntrada' => Entrada::factory(),
            'idPago' => Pago::factory(),
            'idParticipante' => Participante::factory()
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