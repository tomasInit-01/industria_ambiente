<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModuloOrigenToCotioTable extends Migration
{
    public function up()
    {
        Schema::table('cotio', function (Blueprint $table) {
            $table->string('modulo_origen', 20)
                  ->nullable()
                  ->default(null)
                  ->after('enable_ot')
                  ->comment('muestreo|ot - Módulo donde se originó la tarea (null para registros antiguos)');
        });
    }

    public function down()
    {
        Schema::table('cotio', function (Blueprint $table) {
            $table->dropColumn('modulo_origen');
        });
    }
}