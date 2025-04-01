<?php

namespace Database\Seeders;

use App\Models\TipoEntrada;
use Illuminate\Database\Seeder;

class TipoEntradaSeeder extends Seeder
{
    public function run()
    {
        // Tipos VIP
        TipoEntrada::create([
            'nombre' => 'Palco VIP',
            'descripcion' => 'Acceso exclusivo a zona VIP con servicio personalizado',
            'categoria' => 'VIP'
        ]);

        TipoEntrada::create([
            'nombre' => 'Meet & Greet',
            'descripcion' => 'Incluye encuentro con artistas y acceso VIP',
            'categoria' => 'VIP'
        ]);

        // Tipos General
        TipoEntrada::create([
            'nombre' => 'Entrada General',
            'descripcion' => 'Acceso general al evento',
            'categoria' => 'General'
        ]);

        TipoEntrada::create([
            'nombre' => 'Pista',
            'descripcion' => 'Acceso a la zona de pista',
            'categoria' => 'General'
        ]);

        // Tipos Tribuna
        TipoEntrada::create([
            'nombre' => 'Tribuna Norte',
            'descripcion' => 'Asiento en tribuna norte',
            'categoria' => 'Tribuna'
        ]);

        TipoEntrada::create([
            'nombre' => 'Tribuna Sur',
            'descripcion' => 'Asiento en tribuna sur',
            'categoria' => 'Tribuna'
        ]);

        // Tipos Especiales
        TipoEntrada::create([
            'nombre' => 'Early Bird',
            'descripcion' => 'Entrada anticipada con descuento',
            'categoria' => 'Especial'
        ]);

        TipoEntrada::create([
            'nombre' => 'Estudiante',
            'descripcion' => 'Entrada con descuento para estudiantes',
            'categoria' => 'Especial'
        ]);
    }
} 