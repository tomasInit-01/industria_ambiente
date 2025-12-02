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
        // Agregar cotio_item_id a la tabla variables
        // Nota: No se crea foreign key constraint porque cotio_items.id no tiene restricción unique
        Schema::table('variables', function (Blueprint $table) {
            $table->string('cotio_item_id', 50)->nullable()->after('id');
            $table->index('cotio_item_id');
        });

        // Modificar la tabla ley_normativa_variable
        Schema::table('ley_normativa_variable', function (Blueprint $table) {
            // Eliminar columnas que ya no se necesitan
            $table->dropColumn(['es_obligatoria', 'observaciones']);
            
            // Agregar unidad_medida
            $table->string('unidad_medida', 50)->nullable()->after('valor_limite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir cambios en ley_normativa_variable
        Schema::table('ley_normativa_variable', function (Blueprint $table) {
            $table->dropColumn('unidad_medida');
            $table->boolean('es_obligatoria')->default(true)->after('valor_limite');
            $table->text('observaciones')->nullable()->after('es_obligatoria');
        });

        // Revertir cambios en variables
        Schema::table('variables', function (Blueprint $table) {
            $table->dropIndex(['cotio_item_id']);
            $table->dropColumn('cotio_item_id');
        });
    }
};

