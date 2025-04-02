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
        Schema::table('evento', function (Blueprint $table) {
            $table->boolean('es_online')->default(false)->after('lugar');
            $table->string('enlace_streaming')->nullable()->after('es_online');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evento', function (Blueprint $table) {
            $table->dropColumn('es_online');
            $table->dropColumn('enlace_streaming');
        });
    }
};
