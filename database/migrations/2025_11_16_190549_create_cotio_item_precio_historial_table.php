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
        Schema::create('cotio_item_precio_historial', function (Blueprint $table) {
            $table->id();
            $table->string('operacion_id', 36); // UUID para agrupar cambios de una misma operación masiva
            $table->unsignedBigInteger('item_id');
            $table->decimal('precio_anterior', 10, 2)->nullable();
            $table->decimal('precio_nuevo', 10, 2)->nullable();
            $table->string('tipo_cambio', 20); // 'porcentaje', 'valor_fijo', 'manual'
            $table->decimal('valor_aplicado', 10, 2)->nullable(); // El porcentaje o valor fijo aplicado
            $table->text('descripcion')->nullable(); // Descripción de la operación
            $table->string('usuario_id', 20)->nullable();
            $table->boolean('revertido')->default(false);
            $table->timestamp('fecha_cambio')->useCurrent();
            $table->timestamp('fecha_reversion')->nullable();
            $table->string('usuario_reversion_id', 20)->nullable();
            
            // Índices
            $table->index('operacion_id');
            $table->index('item_id');
            $table->index('fecha_cambio');
            $table->index('revertido');
            $table->index('usuario_id');
            
            // Nota: No se agrega foreign key constraint para evitar problemas con la estructura de cotio_items
            // Las relaciones se manejan a nivel de aplicación mediante Eloquent
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotio_item_precio_historial');
    }
};
