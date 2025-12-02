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
        Schema::create('metodos_analisis', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique()->comment('Código único del método de análisis');
            $table->string('nombre')->comment('Nombre del método de análisis');
            $table->text('descripcion')->nullable()->comment('Descripción detallada del método');
            $table->string('equipo_requerido')->nullable()->comment('Equipo necesario para el análisis');
            $table->text('procedimiento')->nullable()->comment('Procedimiento del análisis');
            $table->string('unidad_medicion')->nullable()->comment('Unidad de medición del resultado');
            $table->decimal('limite_deteccion_default', 15, 6)->nullable()->comment('Límite de detección por defecto');
            $table->decimal('limite_cuantificacion_default', 15, 6)->nullable()->comment('Límite de cuantificación por defecto');
            $table->decimal('costo_base', 10, 2)->nullable()->comment('Costo base del análisis');
            $table->integer('tiempo_estimado_horas')->nullable()->comment('Tiempo estimado en horas');
            $table->boolean('requiere_calibracion')->default(false)->comment('Si requiere calibración previa');
            $table->boolean('activo')->default(true)->comment('Si el método está activo');
            $table->timestamps();
            
            // Índices
            $table->index('codigo');
            $table->index('activo');
            $table->index(['activo', 'codigo']);
            $table->index('requiere_calibracion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metodos_analisis');
    }
};
