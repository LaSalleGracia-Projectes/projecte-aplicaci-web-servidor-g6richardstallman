<?php

namespace Database\Factories;

use App\Models\Entrada;
use App\Models\Evento;
use Illuminate\Database\Eloquent\Factories\Factory;

class EntradaFactory extends Factory
{
    protected $model = Entrada::class;

    public function definition()
    {
        // Asegurar que existe un evento al que asociar la entrada
        $evento = Evento::inRandomOrder()->first() ?? Evento::factory()->create();
        
        return [
            'fecha_venta' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'nombre_persona' => $this->faker->name,
            'idEvento' => $evento->idEvento, // Asignar evento existente
            // idTipoEntrada y otros campos se deben añadir si son necesarios
            // y están en la migración de entrada
        ];
    }
}