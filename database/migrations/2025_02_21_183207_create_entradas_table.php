<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('entrada', function (Blueprint $table) {
            $table->id('idEntrada');
            $table->date('fechaVenta');
            $table->string('nombrePersona');
            
            $table->unsignedBigInteger('idEvento');
            $table->foreign('idEvento')->references('idEvento')->on('evento')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('entrada');
    }
};