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
    Schema::create('empresa', function (Blueprint $table) {
        $table->id('idEmpresa');
        $table->string('nombre');
        $table->string('contacto')->nullable();
        $table->string('telefono', 20)->nullable();
        $table->string('email')->unique()->nullable();
        $table->integer('tipoPlan')->nullable();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::dropIfExists('empresa');
}
};
