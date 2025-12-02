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
        Schema::table('cotio_items', function (Blueprint $table) {
            // Agregar columna para método de muestreo (foreign key a metodos_muestreo)
            $table->string('metodo_muestreo', 50)->nullable()->after('matriz_codigo');
            $table->foreign('metodo_muestreo')->references('codigo')->on('metodos_muestreo');
            
            // Agregar columna para límite de cuantificación
            $table->decimal('limite_cuantificacion', 15, 6)->nullable()->after('limites_establecidos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotio_items', function (Blueprint $table) {
            $table->dropForeign(['metodo_muestreo']);
            $table->dropColumn(['metodo_muestreo', 'limite_cuantificacion']);
        });
    }
};

