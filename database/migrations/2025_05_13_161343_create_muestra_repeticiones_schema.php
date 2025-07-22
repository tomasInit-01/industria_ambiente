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
        Schema::create('muestra_repeticiones', function (Blueprint $table) {
            $table->id();
            $table->integer('cotio_numcoti');
            $table->integer('cotio_item');
            $table->integer('cotio_subitem');
            $table->integer('repeticion');
            $table->boolean('completada')->default(false);
            $table->dateTime('fecha_muestreo')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            
            $table->foreign(['cotio_numcoti', 'cotio_item', 'cotio_subitem'])
                  ->references(['cotio_numcoti', 'cotio_item', 'cotio_subitem'])
                  ->on('cotio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('muestra_repeticiones');
    }
};
