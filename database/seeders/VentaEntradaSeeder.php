<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VentaEntrada;

class VentaEntradaSeeder extends Seeder
{
    public function run(): void
    {
        VentaEntrada::factory(10)->create();
    }
}