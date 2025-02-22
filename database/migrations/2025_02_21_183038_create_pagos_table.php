<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pago', function (Blueprint $table) {
            $table->bigIncrements('idPago'); 
            $table->string('nombre');
            $table->string('contacto');
            $table->string('telefono');
            $table->string('email')->unique();
            $table->timestamps();
            $table->softDeletes();
        });
    } 

    public function down()
    {
        Schema::dropIfExists('pago');
    }
};