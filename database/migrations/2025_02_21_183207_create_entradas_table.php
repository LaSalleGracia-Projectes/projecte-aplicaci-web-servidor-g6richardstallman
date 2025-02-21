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
    Schema::create('entrada', function (Blueprint $table) {
        $table->id('idEntrada');
        $table->date('fechaVenta');
        $table->string('nombrePersona')->nullable();
        $table->string('apellido1')->nullable();
        $table->string('apellido2')->nullable();
        $table->unsignedBigInteger('idEvento');
        $table->foreign('idEvento')->references('idEvento')->on('evento')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('entrada');
    }
};
