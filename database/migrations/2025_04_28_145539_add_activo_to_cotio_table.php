<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // En la migración
    public function up(): void
    {
        Schema::table('cotio', function (Blueprint $table) {
            $table->boolean('activo')->default(false)->after('cotio_estado');
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
