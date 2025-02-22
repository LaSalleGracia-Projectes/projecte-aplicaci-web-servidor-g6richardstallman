<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Valoracion;

class ValoracionSeeder extends Seeder
{
    public function run(): void
    {
        Valoracion::factory(10)->create();
    }
}