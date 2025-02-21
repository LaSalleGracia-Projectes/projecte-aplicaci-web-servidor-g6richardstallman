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
    Schema::create('clients', function (Blueprint $table) {
        $table->id('idCliente');
        $table->string('nombre');
        $table->string('apellido1')->nullable();
        $table->string('apellido2')->nullable();
        $table->string('email')->unique();
        $table->string('telefono', 20)->nullable();
        $table->string('dni', 20)->unique();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::dropIfExists('clients');
}
};
