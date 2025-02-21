<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Entrada;

class EntradaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Entrada::factory(100)->create();
    }
}