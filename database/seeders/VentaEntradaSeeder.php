<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VentaEntrada;

class VentaEntradaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        VentaEntrada::factory(50)->create();
    }
}