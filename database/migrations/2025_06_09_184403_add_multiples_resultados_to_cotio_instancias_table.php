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
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->string('resultado_2', 255)->nullable()->after('resultado');
            $table->string('resultado_3', 255)->nullable()->after('resultado_2');
            $table->string('resultado_final', 255)->nullable()->after('resultado_3');
        });
    }
    
    public function down()
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->dropColumn(['resultado_2', 'resultado_3', 'resultado_final']);
        });
    }
};
