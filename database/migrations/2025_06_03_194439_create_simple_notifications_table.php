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
        Schema::create('simple_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('coordinador_codigo'); // Relación con el coordinador
            $table->integer('instancia_id')->nullable(); // Relación con la instancia
            $table->string('mensaje');
            $table->boolean('leida')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simple_notifications');
    }
};
