<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSectorCodigoToUsuTable extends Migration
{
    public function up()
    {
        Schema::table('usu', function (Blueprint $table) {
            $table->string('sector_codigo')->nullable()->after('usu_nivel');

            // Clave foránea hacia la misma tabla (autorelación)
            $table->foreign('sector_codigo')
                  ->references('usu_codigo')
                  ->on('usu')
                  ->onDelete('set null'); // Opcional: si se borra el sector, deja NULL
        });
    }

    public function down()
    {
        Schema::table('usu', function (Blueprint $table) {
            $table->dropForeign(['sector_codigo']);
            $table->dropColumn('sector_codigo');
        });
    }
}
