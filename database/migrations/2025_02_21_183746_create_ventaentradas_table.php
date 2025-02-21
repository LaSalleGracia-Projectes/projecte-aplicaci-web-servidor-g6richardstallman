<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ventaentrada', function (Blueprint $table) {
            $table->id('idVentaEntrada');
            $table->string('dniCliente', 20);
            $table->date('fechaCompra');
            $table->enum('estadoPago', ['Pagat', 'Pendent', 'Cancel·lat'])->default('Pendent');
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('impuestos', 10, 2)->nullable();
            $table->decimal('descuento', 10, 2)->nullable();
            $table->decimal('montoTotal', 10, 2);
            $table->unsignedBigInteger('idEntrada');
            $table->unsignedBigInteger('idPago');
            
            $table->foreign('dniCliente')->references('dni')->on('clients')->onDelete('cascade');
            $table->foreign('idEntrada')->references('idEntrada')->on('entrada')->onDelete('cascade');
            $table->foreign('idPago')->references('idPago')->on('pago')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('ventaentrada');
    }
};
