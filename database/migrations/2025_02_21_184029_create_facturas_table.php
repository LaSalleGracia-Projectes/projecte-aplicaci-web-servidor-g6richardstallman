<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('factura', function (Blueprint $table) {
            $table->id('idFactura');
            $table->string('numero_factura')->unique(); // Número de factura único
            $table->date('fecha_emision'); // Fecha de emisión de la factura
            $table->date('fecha_vencimiento')->nullable(); // Fecha de vencimiento (opcional)
            
            // Información monetaria
            $table->decimal('subtotal', 10, 2); // Base imponible
            $table->decimal('impostos', 10, 2); // IVA u otros impuestos
            $table->decimal('descuento', 10, 2)->default(0); // Descuento si aplica
            $table->decimal('montoTotal', 10, 2); // Monto total
            
            // Estado de la factura
            $table->enum('estado', ['emitida', 'pagada', 'cancelada'])->default('emitida');
            
            // Datos de facturación
            $table->string('nombre_fiscal'); // Nombre fiscal o razón social
            $table->string('nif'); // NIF/CIF/DNI
            $table->text('direccion_fiscal'); // Dirección fiscal completa
            
            // Método de pago
            $table->string('metodo_pago')->nullable(); // Tarjeta, transferencia, etc.
            
            // Notas
            $table->text('notas')->nullable(); // Notas adicionales
            
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