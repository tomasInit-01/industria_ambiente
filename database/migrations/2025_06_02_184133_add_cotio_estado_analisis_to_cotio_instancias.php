<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCotioEstadoAnalisisToCotioInstancias extends Migration
{
    public function up()
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->string('cotio_estado_analisis', 20)->nullable()->after('cotio_estado');
        });
    }

    public function down()
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->dropColumn('cotio_estado_analisis');
        });
    }
}
