<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cotio', function (Blueprint $table) {
            $table->boolean('enable_ot')
                  ->default(false)  // Valor por defecto explícitamente false
                  ->after('coti_estado') // Opcional: especifica después de qué columna debe ir
                  ->comment('Controla si la cotización puede generar órdenes de trabajo (false por defecto)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cotio', function (Blueprint $table) {
            $table->dropColumn('enable_ot');
        });
    }
};