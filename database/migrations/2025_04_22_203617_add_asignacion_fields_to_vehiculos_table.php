<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// database/migrations/xxxx_xx_xx_add_asignacion_fields_to_vehiculos_table.php
public function up()
{
    Schema::table('vehiculos', function (Blueprint $table) {
        $table->string('usuario_id')->nullable();
        $table->date('fecha_inicio')->nullable();
        $table->date('fecha_fin')->nullable();
        $table->enum('estado', ['libre', 'ocupado'])->default('libre');
    });
}

public function down()
{
    Schema::table('vehiculos', function (Blueprint $table) {
        $table->dropColumn(['usuario_id', 'fecha_inicio', 'fecha_fin', 'estado']);
    });
}

};
