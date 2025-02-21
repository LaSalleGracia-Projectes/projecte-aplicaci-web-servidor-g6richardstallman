<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Estadistiques;

class EstadistiquesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Estadistiques::factory(50)->create();
    }
}
