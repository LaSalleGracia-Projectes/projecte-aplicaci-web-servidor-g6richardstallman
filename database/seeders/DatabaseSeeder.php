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
            AdminUserSeeder::class,
            UserSeeder::class,
            OrganizadorSeeder::class,
            ParticipanteSeeder::class,
            EventoSeeder::class,
            EntradaSeeder::class,
            VentaEntradaSeeder::class,
            FacturaSeeder::class,
            PagoSeeder::class,
            ValoracionSeeder::class,
            FavoritoSeeder::class,
            TipoEntradaSeeder::class,
            OrganizadorFavoritoSeeder::class,
        ]);
    }
}