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
            EventoSeeder::class,
            EntradaSeeder::class,
            VentaEntradaSeeder::class,
            FacturaSeeder::class,
            FavoritoSeeder::class,
            TipoEntradaSeeder::class,
            OrganizadorFavoritoSeeder::class,
        ]);
    }
}