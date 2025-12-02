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
        Schema::create('ley_normativa_variable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ley_normativa_id')->constrained('leyes_normativas')->onDelete('cascade');
            $table->foreignId('variable_id')->constrained('variables')->onDelete('cascade');
            $table->string('valor_limite', 255)->nullable(); // Valor específico para esta ley
            $table->boolean('es_obligatoria')->default(true); // Si es obligatoria para esta ley
            $table->text('observaciones')->nullable(); // Observaciones específicas
            $table->timestamps();

            // Índices
            $table->unique(['ley_normativa_id', 'variable_id']);
            $table->index('ley_normativa_id');
            $table->index('variable_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ley_normativa_variable');
    }
};
