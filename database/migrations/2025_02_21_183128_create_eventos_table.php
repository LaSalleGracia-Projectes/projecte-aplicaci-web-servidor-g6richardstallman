<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evento', function (Blueprint $table) {
            $table->id('idEvento');
            $table->string('nombreEvento');
            $table->date('fechaEvento');
            $table->text('descripcion');
            $table->time('hora');
            $table->string('ubicacion');
            $table->string('imagen')->nullable();
            $table->string('categoria');
            $table->string('lugar');
            $table->boolean('es_online')->default(false);
            $table->unsignedBigInteger('idOrganizador');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('idOrganizador')
                  ->references('idOrganizador')
                  ->on('organizador')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evento');
    }
};