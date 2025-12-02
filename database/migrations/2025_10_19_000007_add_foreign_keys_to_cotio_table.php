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
        Schema::table('cotio', function (Blueprint $table) {
            // Agregar foreign keys para las nuevas relaciones
            // Usar cotio_codigometodo existente para muestreo
            $table->foreign('cotio_codigometodo')
                  ->references('codigo')
                  ->on('metodos_muestreo')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
                  
            $table->foreign('cotio_codigometodo_analisis')
                  ->references('codigo')
                  ->on('metodos_analisis')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
                  
            $table->foreign('ley_aplicacion')
                  ->references('codigo')
                  ->on('leyes_normativas')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotio', function (Blueprint $table) {
            // Eliminar foreign keys
            $table->dropForeign(['cotio_codigometodo']);
            $table->dropForeign(['cotio_codigometodo_analisis']);
            $table->dropForeign(['ley_aplicacion']);
        });
    }
};
