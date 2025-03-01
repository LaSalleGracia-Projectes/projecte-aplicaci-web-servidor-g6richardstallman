<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizadorTable extends Migration
{
    public function up()
    {
        Schema::create('organizador', function (Blueprint $table) {
            $table->id('idOrganizador');
            $table->string('nombre_organizacion');
            $table->string('telefono_contacto');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->softDeletes();

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
}