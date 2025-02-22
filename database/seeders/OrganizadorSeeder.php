<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organizador;

class OrganizadorSeeder extends Seeder
{
    public function run(): void
    {
        Organizador::factory(10)->create();
    }
}