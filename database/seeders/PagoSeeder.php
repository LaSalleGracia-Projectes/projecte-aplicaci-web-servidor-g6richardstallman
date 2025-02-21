<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pago;

class PagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Pago::factory(20)->create();
    }
}