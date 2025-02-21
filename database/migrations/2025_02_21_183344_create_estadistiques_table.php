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
    Schema::create('estadistiques', function (Blueprint $table) {
        $table->id('idEstadistiques');
        $table->unsignedBigInteger('idEmpresa');
        $table->unsignedBigInteger('idEvento');
        $table->integer('visitesTotals')->default(0);
        $table->integer('entradesVendes')->default(0);
        $table->integer('tipusPlan')->nullable();
        $table->decimal('valoracioProm', 3, 2)->nullable();
        
        $table->foreign('idEmpresa')->references('idEmpresa')->on('empresa')->onDelete('cascade');
        $table->foreign('idEvento')->references('idEvento')->on('evento')->onDelete('cascade');
        $table->foreign('tipusPlan')->references('idPlan')->on('plan')->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::dropIfExists('estadistiques');
}
};
