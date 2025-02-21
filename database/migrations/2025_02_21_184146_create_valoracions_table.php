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
        Schema::create('valoracion', function (Blueprint $table) {
            $table->id('idValoracion');
            $table->unsignedBigInteger('idCliente');
            $table->unsignedBigInteger('idEvento');
            $table->integer('puntuacion')->check('puntuacion BETWEEN 1 AND 5');
            $table->text('comentario')->nullable();
            
            // Foreign Keys
            $table->foreign('idCliente')->references('idCliente')->on('clients')->onDelete('cascade');
            $table->foreign('idEvento')->references('idEvento')->on('evento')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('valoracion');
    }
};
