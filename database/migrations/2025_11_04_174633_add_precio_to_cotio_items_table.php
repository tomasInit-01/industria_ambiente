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
        Schema::table('cotio_items', function (Blueprint $table) {
            // Agregar columna precio (solo para componentes, no para muestras)
            // Permite valores de hasta 99,999,999.99 (7 dígitos antes del decimal)
            $table->decimal('precio', 10, 2)->nullable()->after('unidad_medida');
        });

        // Actualizar registros existentes que no son muestras con precio por defecto
        DB::table('cotio_items')
            ->where('es_muestra', false)
            ->whereNull('precio')
            ->update(['precio' => 5000.00]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotio_items', function (Blueprint $table) {
            $table->dropColumn('precio');
        });
    }
};
