<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cotio', function (Blueprint $table) {
            $table->string('cotio_estado', 20)
                  ->default('Pendiente')
                  ->comment('Estado de la tarea: Pendiente, En Proceso, Finalizada');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotio', function (Blueprint $table) {
            //
        });
    }
};
