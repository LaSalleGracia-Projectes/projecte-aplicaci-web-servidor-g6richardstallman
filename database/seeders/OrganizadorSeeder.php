<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organizador;

class OrganizadorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Organizador::factory(5)->create();
    }
}