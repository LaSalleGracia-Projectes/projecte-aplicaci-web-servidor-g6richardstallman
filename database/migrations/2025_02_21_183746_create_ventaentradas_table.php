<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('venta_entrada', function (Blueprint $table) {
            $table->id('idVentaEntrada');
            $table->enum('estado_pago', ['Pagado', 'Pendiente'])->default('Pendiente');
            $table->decimal('impuestos', 10, 2);
            $table->decimal('precio', 10, 2);
            $table->timestamp('fecha_compra');

            // Foreign Keys
            $table->unsignedBigInteger('idEntrada');
            $table->unsignedBigInteger('idPago')->nullable();
            $table->unsignedBigInteger('idParticipante');

            $table->foreign('idEntrada')
                ->references('idEntrada')
                ->on('entrada')
                ->onDelete('cascade');

            $table->foreign('idPago')
                ->references('idPago')
                ->on('pago')
                ->onDelete('cascade');

            $table->foreign('idParticipante')
                ->references('idParticipante')
                ->on('participante')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('venta_entrada');
    }
};