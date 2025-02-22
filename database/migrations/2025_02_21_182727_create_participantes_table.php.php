<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('participante', function (Blueprint $table) {
            $table->id('idParticipante');
            $table->string('dni')->unique();
            $table->string('telefono');
            
            $table->unsignedBigInteger('idUser');
            $table->foreign('idUser')->references('idUser')->on('users')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('participante');
    }
};