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
            $table->boolean('firmado')->default(false)->after('aprobado_informe');
            $table->string('identificador_documento_firma')->nullable()->after('firmado');
            $table->timestamp('fecha_firma')->nullable()->after('identificador_documento_firma');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->dropColumn(['firmado', 'identificador_documento_firma', 'fecha_firma']);
        });
    }
};
