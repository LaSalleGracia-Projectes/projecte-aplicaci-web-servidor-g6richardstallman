<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('organizador_favorito', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idParticipante');
            $table->unsignedBigInteger('idOrganizador');
            $table->timestamps();
            
            // Claves foráneas
            $table->foreign('idParticipante')->references('idParticipante')->on('participante')->onDelete('cascade');
            $table->foreign('idOrganizador')->references('idOrganizador')->on('organizador')->onDelete('cascade');
            
            // Asegurar que la combinación sea única
            $table->unique(['idParticipante', 'idOrganizador']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('organizador_favorito');
    }
};