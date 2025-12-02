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
            // Eliminar columnas obsoletas de la tabla cotio
            $table->dropColumn([
                'cotio_codigonormativa',
                'cotio_estado',
                'fecha_inicio',
                'fecha_fin',
                'vehiculo_asignado',
                'es_frecuente',
                'frecuencia_dias',
                'enable_ot',
                'modulo_origen',
                'active_ot',
                'cotio_identificacion',
                'muestreo_contador',
                'volumen_muestra'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotio', function (Blueprint $table) {
            // Restaurar columnas eliminadas
            $table->char('cotio_codigonormativa', 5)->nullable();
            $table->string('cotio_estado', 20)->nullable()->default('Pendiente');
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
            $table->bigInteger('vehiculo_asignado')->nullable();
            $table->boolean('es_frecuente')->default(false);
            $table->string('frecuencia_dias', 50)->nullable();
            $table->boolean('enable_ot')->default(false);
            $table->string('modulo_origen', 20)->nullable();
            $table->boolean('active_ot')->default(false);
            $table->string('cotio_identificacion', 100)->nullable();
            $table->string('muestreo_contador', 255)->nullable();
            $table->decimal('volumen_muestra', 10, 2)->nullable();
        });
    }
};
