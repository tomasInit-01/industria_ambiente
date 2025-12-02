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
        Schema::create('leyes_normativas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique()->comment('Código único de la ley/normativa');
            $table->string('nombre')->comment('Nombre de la ley/normativa');
            $table->string('grupo')->comment('Grupo al que pertenece (ej: Código Alimentario Argentino)');
            $table->string('articulo')->nullable()->comment('Artículo específico (ej: Art. 982)');
            $table->text('descripcion')->nullable()->comment('Descripción de la normativa');
            $table->text('variables_aplicables')->nullable()->comment('Variables que aplican a esta normativa');
            $table->string('organismo_emisor')->nullable()->comment('Organismo que emite la normativa');
            $table->date('fecha_vigencia')->nullable()->comment('Fecha de vigencia');
            $table->date('fecha_actualizacion')->nullable()->comment('Última actualización');
            $table->text('observaciones')->nullable()->comment('Observaciones adicionales');
            $table->boolean('activo')->default(true)->comment('Si la normativa está activa');
            $table->timestamps();
            
            // Índices
            $table->index('codigo');
            $table->index('grupo');
            $table->index('activo');
            $table->index(['grupo', 'activo']);
            $table->index(['codigo', 'activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leyes_normativas');
    }
};
