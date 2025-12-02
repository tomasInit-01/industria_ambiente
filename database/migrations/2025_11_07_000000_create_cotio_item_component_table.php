<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotio_item_component', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agrupador_id');
            $table->unsignedBigInteger('componente_id');
            $table->timestamps();

            $table->index('agrupador_id');
            $table->index('componente_id');
            $table->unique(['agrupador_id', 'componente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotio_item_component');
    }
};
