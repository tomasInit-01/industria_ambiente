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
        Schema::table('cotio_inventario_muestreo', function (Blueprint $table) {
            $table->integer('instance_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotio_inventario_muestreo', function (Blueprint $table) {
            $table->dropColumn('instance_number');
        });
    }
};
