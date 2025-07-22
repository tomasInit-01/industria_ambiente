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
        Schema::create('cotio_valores_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotio_instancia_id')->constrained('cotio_instancias')->onDelete('cascade');
            $table->string('variable'); 
            $table->string('valor')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotio_valores_variables');
    }
};
