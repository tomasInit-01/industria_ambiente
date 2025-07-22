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
            $table->unsignedBigInteger('cotio_id')->nullable()->after('id');
            $table->foreign('cotio_id')->references('id')->on('cotios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('variables_requeridas', function (Blueprint $table) {
            $table->dropForeign(['cotio_id']);
            $table->dropColumn('cotio_id');
        });
    }
};
