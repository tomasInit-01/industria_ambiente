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
        Schema::table('variables_requeridas', function (Blueprint $table) {
            $table->string('unidad_medicion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('variables_requeridas', function (Blueprint $table) {
            $table->dropColumn('unidad_medicion');
        });
    }
};
