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
        Schema::create('variables_requeridas', function (Blueprint $table) {
            $table->id();
            $table->string('cotio_descripcion'); 
            $table->string('nombre'); 
            $table->boolean('obligatorio')->default(true);
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variables_requeridas');
    }
};
