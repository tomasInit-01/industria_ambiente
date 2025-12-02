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
        Schema::create('variables', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('unidad_medicion', 50)->nullable();
            $table->string('tipo_variable', 100)->nullable(); // fisico-quimica, microbiologica, etc.
            $table->text('metodo_determinacion')->nullable();
            $table->decimal('limite_minimo', 15, 6)->nullable();
            $table->decimal('limite_maximo', 15, 6)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Índices
            $table->index('codigo');
            $table->index('tipo_variable');
            $table->index('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variables');
    }
};
