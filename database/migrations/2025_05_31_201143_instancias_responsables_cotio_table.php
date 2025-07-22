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
        Schema::create('instancia_responsable_muestreo', function (Blueprint $table) {
            $table->unsignedBigInteger('cotio_instancia_id');
            $table->string('usu_codigo'); // usu_codigo del responsable
            $table->timestamps();
            
            $table->foreign('cotio_instancia_id')->references('id')->on('cotio_instancias')->onDelete('cascade');
            $table->foreign('usu_codigo')->references('usu_codigo')->on('usu')->onDelete('cascade');
        });
        
        Schema::create('instancia_responsable_analisis', function (Blueprint $table) {
            $table->unsignedBigInteger('cotio_instancia_id');
            $table->string('usu_codigo'); // usu_codigo del responsable
            $table->timestamps();
            
            $table->foreign('cotio_instancia_id')->references('id')->on('cotio_instancias')->onDelete('cascade');
            $table->foreign('usu_codigo')->references('usu_codigo')->on('usu')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instancia_responsable_muestreo');
        Schema::dropIfExists('instancia_responsable_analisis');
    }
};
