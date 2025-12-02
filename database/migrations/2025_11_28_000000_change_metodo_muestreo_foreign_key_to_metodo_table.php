<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero eliminar la foreign key actual que apunta a metodos_muestreo
        Schema::table('cotio_items', function (Blueprint $table) {
            $table->dropForeign(['metodo_muestreo']);
        });

        // Limpiar valores que no existen en la tabla metodo
        // Poner en NULL los valores de metodo_muestreo que no existen en metodo.metodo_codigo
        DB::statement('
            UPDATE cotio_items 
            SET metodo_muestreo = NULL 
            WHERE metodo_muestreo IS NOT NULL 
            AND metodo_muestreo NOT IN (SELECT metodo_codigo FROM metodo)
        ');

        // Crear nueva foreign key que apunta a metodo.metodo_codigo
        Schema::table('cotio_items', function (Blueprint $table) {
            $table->foreign('metodo_muestreo')
                  ->references('metodo_codigo')
                  ->on('metodo')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotio_items', function (Blueprint $table) {
            // Eliminar la foreign key que apunta a metodo
            $table->dropForeign(['metodo_muestreo']);
        });

        Schema::table('cotio_items', function (Blueprint $table) {
            // Restaurar la foreign key original que apunta a metodos_muestreo
            $table->foreign('metodo_muestreo')
                  ->references('codigo')
                  ->on('metodos_muestreo')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }
};

