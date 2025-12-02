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
            // Renombrar columna existente para muestreo (si es necesario)
            // La columna cotio_codigometodo ya existe, la usaremos para muestreo
            
            // Agregar nueva columna para método de análisis
            $table->char('cotio_codigometodo_analisis', 15)->nullable()->after('cotio_codigometodo');
            
            // Agregar nuevas columnas para límites
            $table->decimal('limite_deteccion', 15, 6)->nullable()->after('cotio_precio');
            $table->decimal('limite_cuantificacion', 15, 6)->nullable()->after('limite_deteccion');
            
            // Agregar columna para ley de aplicación
            $table->string('ley_aplicacion')->nullable()->after('limite_cuantificacion');
            
            // Agregar índices para mejorar rendimiento
            $table->index('cotio_codigometodo');
            $table->index('cotio_codigometodo_analisis');
            $table->index('ley_aplicacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotio', function (Blueprint $table) {
            // Eliminar índices primero
            $table->dropIndex(['cotio_codigometodo']);
            $table->dropIndex(['cotio_codigometodo_analisis']);
            $table->dropIndex(['ley_aplicacion']);
            
            // Eliminar columnas
            $table->dropColumn([
                'cotio_codigometodo_analisis',
                'limite_deteccion',
                'limite_cuantificacion',
                'ley_aplicacion'
            ]);
        });
    }
};
