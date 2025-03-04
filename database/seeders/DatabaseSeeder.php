<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            OrganizadorSeeder::class,
            ParticipanteSeeder::class,
            EventoSeeder::class,
            EntradaSeeder::class,
            FacturaSeeder::class,
            PagoSeeder::class,
            ValoracionSeeder::class,
            VentaEntradaSeeder::class,
            FavoritoSeeder::class
        ]);
    }
}