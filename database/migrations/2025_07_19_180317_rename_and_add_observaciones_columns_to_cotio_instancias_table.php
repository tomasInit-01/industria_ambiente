<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->renameColumn('observaciones_medicion', 'observaciones_medicion_muestreador');
            $table->text('observaciones_medicion_coord_muestreo')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->renameColumn('observaciones_medicion_muestreador', 'observaciones_medicion');
            $table->dropColumn('observaciones_medicion_coord_muestreo');
        });
    }
};
