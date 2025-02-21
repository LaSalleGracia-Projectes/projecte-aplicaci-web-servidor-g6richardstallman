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
        Schema::create('visita', function (Blueprint $table) {
            $table->id('idVisita');
            $table->unsignedBigInteger('idClient');
            $table->unsignedBigInteger('idEmpresa');
            $table->unsignedBigInteger('idEvent');
            $table->date('fechaVisita');
            $table->enum('tipoVisita', ['SiFavorito', 'NoFavorito'])->default('NoFavorito');
            
            $table->foreign('idClient')->references('idCliente')->on('clients')->onDelete('cascade');
            $table->foreign('idEmpresa')->references('idEmpresa')->on('empresa')->onDelete('cascade');
            $table->foreign('idEvent')->references('idEvento')->on('evento')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('visita');
    }
};
