<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('evento', function (Blueprint $table) {
            $table->id('idEvento');
            $table->string('nombreEvento');
            $table->date('fechaEvento');
            $table->string('lugar');
            
            $table->unsignedBigInteger('idOrganizador');
            $table->foreign('idOrganizador')->references('idOrganizador')->on('organizador')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('evento');
    }
};