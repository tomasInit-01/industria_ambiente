<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToCotioInstanciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            // Campos de asignación
            $table->string('coordinador_codigo', 20)->nullable()->after('responsable_muestreo');
            $table->timestamp('fecha_coordinacion')->nullable()->after('coordinador_codigo');
            $table->integer('vehiculo_asignado')->nullable()->after('fecha_coordinacion');
            
            // Campos de fechas
            $table->timestamp('fecha_inicio')->nullable()->after('vehiculo_asignado');
            $table->timestamp('fecha_fin')->nullable()->after('fecha_inicio');
            
            // Campos de identificación de muestra
            $table->string('cotio_identificacion', 100)->nullable()->after('fecha_fin');
            $table->decimal('volumen_muestra', 10, 2)->nullable()->after('cotio_identificacion');
            
            // Campos de estado y control
            $table->boolean('enable_ot')->default(false)->after('volumen_muestra');
            $table->string('cotio_estado', 20)->default('pendiente')->after('enable_ot');
            
            // Campos de mediciones
            $table->decimal('temperatura', 5, 2)->nullable()->after('cotio_estado');
            $table->decimal('humedad', 5, 2)->nullable()->after('temperatura');
            $table->decimal('presion', 7, 2)->nullable()->after('humedad');
            $table->decimal('velocidad_viento', 5, 2)->nullable()->after('presion');
            $table->text('observaciones_medicion')->nullable()->after('velocidad_viento');
            
            // Índices para mejorar el rendimiento
            $table->index(['cotio_numcoti', 'cotio_item', 'cotio_subitem']);
            $table->index(['instance_number']);
            $table->index(['responsable_muestreo']);
            $table->index(['coordinador_codigo']);
            $table->index(['vehiculo_asignado']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->dropColumn([
                'coordinador_codigo',
                'fecha_coordinacion',
                'vehiculo_asignado',
                'fecha_inicio',
                'fecha_fin',
                'cotio_identificacion',
                'volumen_muestra',
                'enable_ot',
                'cotio_estado',
                'temperatura',
                'humedad',
                'presion',
                'velocidad_viento',
                'observaciones_medicion'
            ]);
            
            $table->dropIndex(['cotio_numcoti', 'cotio_item', 'cotio_subitem']);
            $table->dropIndex(['instance_number']);
            $table->dropIndex(['responsable_muestreo']);
            $table->dropIndex(['coordinador_codigo']);
            $table->dropIndex(['vehiculo_asignado']);
        });
    }
}