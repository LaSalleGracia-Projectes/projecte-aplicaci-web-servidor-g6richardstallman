<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pago', function (Blueprint $table) {
            $table->id('idPago');
            $table->string('nombre');
            $table->string('contacto')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->unique()->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pago');
    }
};