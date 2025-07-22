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
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->string('responsable_resultado_1', 20)->nullable()->after('observacion_resultado');
            $table->string('responsable_resultado_2', 20)->nullable()->after('observacion_resultado_2');
            $table->string('responsable_resultado_3', 20)->nullable()->after('observacion_resultado_3');
            $table->string('responsable_resultado_final', 20)->nullable()->after('observacion_resultado_final');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->dropColumn(['responsable_resultado_1', 'responsable_resultado_2', 'responsable_resultado_3', 'responsable_resultado_final']);
        });
    }
};
