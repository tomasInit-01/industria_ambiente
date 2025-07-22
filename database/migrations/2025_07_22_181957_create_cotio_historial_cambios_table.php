<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('cotio_historial_cambios', function (Blueprint $table) {
            $table->id();
            $table->string('tabla_afectada', 50); 
            $table->unsignedBigInteger('registro_id'); 
            $table->string('campo_modificado', 50); 
            $table->text('valor_anterior')->nullable(); 
            $table->text('valor_nuevo')->nullable(); 
            $table->string('usuario_id', 20)->nullable(); 
            $table->timestamp('fecha_cambio')->useCurrent(); 
            $table->string('ip_origen', 45)->nullable(); 
            $table->string('accion', 10); 
            
            // Índices para optimizar consultas
            $table->index(['tabla_afectada', 'registro_id']);
            $table->index(['fecha_cambio']);
            $table->index(['usuario_id']);
            $table->index(['tabla_afectada', 'campo_modificado']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cotio_historial_cambios');
    }
};