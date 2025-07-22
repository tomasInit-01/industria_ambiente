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
            $table->dateTime('fecha_inicio')->nullable()->after('cotio_responsable_codigo');
            $table->dateTime('fecha_fin')->nullable()->after('fecha_inicio');
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('cotio', function (Blueprint $table) {
            $table->dropColumn(['fecha_inicio', 'fecha_fin']);
        });
    }
    
};
