<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('valoracion', function (Blueprint $table) {
            $table->id('idValoracion');
            $table->integer('puntuacion');
            $table->text('comentario')->nullable();

            $table->unsignedBigInteger('idEvento');
            $table->unsignedBigInteger('idParticipante');

            $table->foreign('idEvento')->references('idEvento')->on('evento')->onDelete('cascade');
            $table->foreign('idParticipante')->references('idParticipante')->on('participante')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('valoracion');
    }
};