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
        Schema::create('newsletter', function (Blueprint $table) {
            $table->id('idNewsletter');
            $table->boolean('spotNotification')->default(false);
            $table->date('fechaEvento');
            $table->text('contenido')->nullable();
            $table->unsignedBigInteger('idEvento');
            $table->foreign('idEvento')->references('idEvento')->on('evento')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('newsletter');
    }
};
