<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Factura;
use App\Models\Participante;
use App\Models\Entrada;
use App\Models\Pago;
use Illuminate\Support\Facades\Schema;

class FacturaSeeder extends Seeder
{
    public function run(): void
    {
        // Verificar si las tablas existen
        if (!Schema::hasTable('participante') || !Schema::hasTable('entrada') || !Schema::hasTable('pago')) {
            $this->command->info('Las tablas necesarias no existen. Ejecutando migraciones...');
            $this->call([
                DatabaseSeeder::class
            ]);
            return;
        }

        // Verificar si hay participantes, entradas y pagos
        $participantes = Participante::all();
        $entradas = Entrada::all();
        $pagos = Pago::all();

        if ($participantes->isEmpty()) {
            $this->command->info('No hay participantes. Ejecutando ParticipanteSeeder...');
            $this->call(ParticipanteSeeder::class);
            $participantes = Participante::all();
        }

        if ($entradas->isEmpty()) {
            $this->command->info('No hay entradas. Ejecutando EntradaSeeder...');
            $this->call(EntradaSeeder::class);
            $entradas = Entrada::all();
        }

        if ($pagos->isEmpty()) {
            $this->command->info('No hay pagos. Ejecutando PagoSeeder...');
            $this->call(PagoSeeder::class);
            $pagos = Pago::all();
        }

        // Crear facturas solo si tenemos los datos necesarios
        if (!$participantes->isEmpty() && !$entradas->isEmpty() && !$pagos->isEmpty()) {
        Factura::factory(10)->create();
            $this->command->info('Facturas creadas exitosamente.');
        } else {
            $this->command->error('No se pudieron crear facturas debido a la falta de datos necesarios.');
        }
    }
}