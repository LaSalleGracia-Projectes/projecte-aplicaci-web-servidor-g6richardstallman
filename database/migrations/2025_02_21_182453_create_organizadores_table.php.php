<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizador', function (Blueprint $table) {
            $table->bigIncrements('idOrganizador');
            $table->string('nombre_organizacion');
            $table->string('telefono_contacto');
            $table->unsignedBigInteger('user_id')->unique();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')
                ->references('idUser')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizador');
    }
};