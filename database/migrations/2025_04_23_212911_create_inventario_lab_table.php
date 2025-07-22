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
        Schema::create('inventario_lab', function (Blueprint $table) {
            $table->id();
            $table->string('equipamiento');
            $table->string('marca_modelo');
            $table->string('n_serie_lote');
            $table->string('codigo_ficha');
            $table->text('observaciones')->nullable();
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario_lab');
    }
};
