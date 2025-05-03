<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tipo_entrada', function (Blueprint $table) {
            $table->id('idTipoEntrada');
            $table->unsignedBigInteger('idEvento');
            $table->string('nombre', 100);
            $table->decimal('precio', 10, 2);
            $table->integer('cantidad_disponible')->nullable(); // Null para eventos online (entradas ilimitadas)
            $table->integer('entradas_vendidas')->default(0);
            $table->text('descripcion')->nullable();
            $table->boolean('es_ilimitado')->default(false); // Para eventos online
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('idEvento')
                  ->references('idEvento')
                  ->on('evento')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tipo_entrada');
    }
}; 