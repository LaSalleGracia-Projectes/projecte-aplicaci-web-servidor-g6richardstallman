<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('entrada', function (Blueprint $table) {
            $table->id('idEntrada');
            $table->dateTime('fecha_venta')->nullable();
            $table->string('nombre_persona')->nullable();
            $table->decimal('precio', 10, 2)->default(0);
            $table->string('codigo')->nullable()->unique();
            $table->enum('estado', ['disponible', 'vendida', 'cancelada'])->default('disponible');
            
            $table->unsignedBigInteger('idEvento');
            $table->foreign('idEvento')->references('idEvento')->on('evento')->onDelete('cascade');
            
            $table->unsignedBigInteger('idTipoEntrada');
            $table->foreign('idTipoEntrada')->references('idTipoEntrada')->on('tipo_entrada')->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entrada');
    }
};