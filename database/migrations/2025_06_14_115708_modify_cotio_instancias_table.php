<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyCotioInstanciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            // Renombrar columnas existentes
            $table->renameColumn('fecha_inicio', 'fecha_inicio_muestreo');
            $table->renameColumn('fecha_fin', 'fecha_fin_muestreo');
            
            // Añadir nuevas columnas
            $table->timestamp('fecha_inicio_ot')->nullable()->after('fecha_fin_muestreo');
            $table->timestamp('fecha_fin_ot')->nullable()->after('fecha_inicio_ot');
            $table->timestamp('fecha_creacion_inform')->nullable()->after('fecha_fin_ot');
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
            // Revertir los cambios
            $table->renameColumn('fecha_inicio_muestreo', 'fecha_inicio');
            $table->renameColumn('fecha_fin_muestreo', 'fecha_fin');
            
            $table->dropColumn(['fecha_inicio_ot', 'fecha_fin_ot', 'fecha_creacion_inform']);
        });
    }
}