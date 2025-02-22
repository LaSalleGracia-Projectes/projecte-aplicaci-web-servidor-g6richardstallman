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
            $table->decimal('subtotal', 10, 2);
            $table->decimal('impuestos', 10, 2);
            $table->decimal('descuento', 10, 2)->nullable();
            $table->decimal('monto_total', 10, 2);

            // Foreign Keys
            $table->unsignedBigInteger('idEntrada');
            $table->unsignedBigInteger('idPago');
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