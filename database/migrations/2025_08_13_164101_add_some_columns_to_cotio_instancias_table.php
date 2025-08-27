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
            $table->integer('time_annulled')->nullable();
            $table->boolean('request_review')->default(false);
            $table->date('fecha_carga_resultado_1')->nullable();
            $table->date('fecha_carga_resultado_2')->nullable();
            $table->date('fecha_carga_resultado_3')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->dropColumn('time_annulled');
            $table->dropColumn('request_review');
            $table->dropColumn('fecha_carga_resultado_1');
            $table->dropColumn('fecha_carga_resultado_2');
            $table->dropColumn('fecha_carga_resultado_3');
        });
    }
};
