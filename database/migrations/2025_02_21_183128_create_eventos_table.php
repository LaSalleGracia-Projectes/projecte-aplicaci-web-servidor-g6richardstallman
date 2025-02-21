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
    Schema::create('evento', function (Blueprint $table) {
        $table->id('idEvento');
        $table->unsignedBigInteger('idEmpresa');
        $table->string('nombreEvento');
        $table->date('fechaEvento');
        $table->string('lugar')->nullable();
        $table->foreign('idEmpresa')->references('idEmpresa')->on('empresa')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('evento');
    }
};
