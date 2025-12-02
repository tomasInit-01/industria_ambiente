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
        Schema::table('cotio_items', function (Blueprint $table) {
            $table->string('matriz_codigo', 10)->nullable()->after('metodo');
            $table->foreign('matriz_codigo')->references('matriz_codigo')->on('matriz');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotio_items', function (Blueprint $table) {
            $table->dropColumn('matriz_codigo');
        });
    }
};
