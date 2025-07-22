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
            $table->boolean('enable_muestreo')->default(false)->after('instance_number');
        });
    }
    
    public function down()
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->dropColumn('enable_muestreo');
        });
    }
};
