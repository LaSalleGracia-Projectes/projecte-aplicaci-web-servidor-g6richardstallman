<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoEntrada;
use App\Models\Evento;

class TipoEntradaSeeder extends Seeder
{
    public function run()
    {
        // No creamos tipos de entrada por defecto
        // Los tipos de entrada serán creados por los organizadores
        // al crear o editar sus eventos
    }
} 