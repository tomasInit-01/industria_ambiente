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
        Schema::table('cotio', function (Blueprint $table) {
            $table->string('cotio_identificacion', 100)
                  ->nullable()
                  ->after('cotio_descripcion')
                  ->comment('Identificación adicional para la muestra');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotio', function (Blueprint $table) {
            $table->dropColumn('cotio_identificacion');
        });
    }
};