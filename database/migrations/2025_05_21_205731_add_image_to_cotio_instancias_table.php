<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->string('image', 255)->nullable()->after('fecha_fin')->comment('Ruta de la imagen asociada a la instancia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
