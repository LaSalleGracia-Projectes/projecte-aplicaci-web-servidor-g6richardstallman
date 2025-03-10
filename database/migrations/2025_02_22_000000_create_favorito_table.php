<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('favorito', function (Blueprint $table) {
            $table->id('idFavorito');
            $table->unsignedBigInteger('idParticipante');
            $table->unsignedBigInteger('idEvento');
            $table->timestamp('fechaAgregado')->useCurrent();
            $table->timestamps();

            // Claves foráneas
            $table->foreign('idParticipante')
                  ->references('idParticipante')
                  ->on('participante')
                  ->onDelete('cascade');
                  
            $table->foreign('idEvento')
                  ->references('idEvento')
                  ->on('evento')
                  ->onDelete('cascade');
                  
            // Índice único para evitar duplicados
            $table->unique(['idParticipante', 'idEvento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorito');
    }
}; 