<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Entrada;

class EntradaSeeder extends Seeder
{
    public function run(): void
    {
        Entrada::factory(15)->create();
    }
}