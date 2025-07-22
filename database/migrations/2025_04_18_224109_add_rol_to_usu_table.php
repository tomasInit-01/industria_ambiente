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
        Schema::table('usu', function (Blueprint $table) {
            $table->string('rol')->nullable()->after('usu_nivel'); // ajustá el after al campo que prefieras
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usu', function (Blueprint $table) {
            $table->dropColumn('rol');
        });
    }
};
