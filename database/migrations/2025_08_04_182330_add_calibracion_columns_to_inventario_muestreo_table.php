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
        Schema::table('inventario_muestreo', function (Blueprint $table) {
            $table->boolean('activo')->default(true);
            $table->date('fecha_calibracion')->nullable();
            $table->string('certificado')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventario_muestreo', function (Blueprint $table) {
            $table->dropColumn('activo');
            $table->dropColumn('fecha_calibracion');
            $table->dropColumn('certificado');
        });
    }
};
