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
        Schema::create('metodos_muestreo', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique()->comment('Código único del método de muestreo');
            $table->string('nombre')->comment('Nombre del método de muestreo');
            $table->text('descripcion')->nullable()->comment('Descripción detallada del método');
            $table->string('equipo_requerido')->nullable()->comment('Equipo necesario para el método');
            $table->text('procedimiento')->nullable()->comment('Procedimiento del método');
            $table->string('unidad_medicion')->nullable()->comment('Unidad de medición asociada');
            $table->decimal('costo_base', 10, 2)->nullable()->comment('Costo base del método');
            $table->boolean('activo')->default(true)->comment('Si el método está activo');
            $table->timestamps();
            
            // Índices
            $table->index('codigo');
            $table->index('activo');
            $table->index(['activo', 'codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metodos_muestreo');
    }
};
