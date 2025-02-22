<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('factura', function (Blueprint $table) {
            $table->id('idFactura');
            $table->decimal('montoTotal', 10, 2);
            $table->decimal('descuento', 10, 2)->nullable();
            $table->decimal('impostos', 10, 2)->nullable();
            $table->decimal('subtotal', 10, 2);

            // Foreign Keys
            $table->unsignedBigInteger('idParticipante');
            $table->unsignedBigInteger('idEntrada');
            $table->unsignedBigInteger('idPago');
            
            $table->foreign('idParticipante')
                  ->references('idParticipante')
                  ->on('participante')
                  ->onDelete('cascade');

            $table->foreign('idEntrada')
                  ->references('idEntrada')
                  ->on('entrada')
                  ->onDelete('cascade');

            $table->foreign('idPago')
                  ->references('idPago')
                  ->on('pago')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('factura');
    }
};