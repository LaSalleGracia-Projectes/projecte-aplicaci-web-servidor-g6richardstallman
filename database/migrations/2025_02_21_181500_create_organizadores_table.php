<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('organizador', function (Blueprint $table) {
            $table->id('idOrganizador');
            $table->string('nombre_organizacion');
            $table->string('telefono_contacto');
            $table->string('direccion_fiscal')->nullable();
            $table->string('cif')->nullable();
            $table->bigInteger('user_id')->unsigned();
            $table->timestamps();

            // Relación con cascada
            $table->foreign('user_id')
                  ->references('idUser')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('organizador');
    }
};