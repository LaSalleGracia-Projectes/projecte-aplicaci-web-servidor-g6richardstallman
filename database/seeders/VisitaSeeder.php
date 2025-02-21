<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Visita;

class VisitaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Visita::factory(50)->create();
    }
}