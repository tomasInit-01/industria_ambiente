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
        Schema::table('cotio', function (Blueprint $table) {
            // Eliminar la foreign key actual que apunta a metodos_muestreo
            $table->dropForeign(['cotio_codigometodo']);
        });

        Schema::table('cotio', function (Blueprint $table) {
            // Crear nueva foreign key que apunta a metodo.metodo_codigo
            $table->foreign('cotio_codigometodo')
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
        Schema::table('cotio', function (Blueprint $table) {
            // Eliminar la foreign key que apunta a metodo
            $table->dropForeign(['cotio_codigometodo']);
        });

        Schema::table('cotio', function (Blueprint $table) {
            // Restaurar la foreign key original que apunta a metodos_muestreo
            $table->foreign('cotio_codigometodo')
                  ->references('codigo')
                  ->on('metodos_muestreo')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
    }
};
