<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento', function (Blueprint $table) {
            $table->bigIncrements('idEvento');
            $table->string('nombreEvento');
            $table->date('fechaEvento');
            $table->time('horaEvento');
            $table->text('descripcion');
            $table->integer('aforo');
            $table->decimal('precioMinimo', 8, 2);
            $table->decimal('precioMaximo', 8, 2);
            $table->string('ubicacion');
            $table->string('imagen')->default('eventos/default.jpg');
            $table->string('categoria');
            $table->string('lugar');
            $table->unsignedBigInteger('idOrganizador');
            $table->boolean('es_online')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('idOrganizador')
                ->references('idUser')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento');
    }
}; 