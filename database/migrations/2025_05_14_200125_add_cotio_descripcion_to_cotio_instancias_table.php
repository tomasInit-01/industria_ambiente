<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->string('cotio_descripcion', 255)->nullable()->after('cotio_subitem');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->dropColumn('cotio_descripcion');
        });
    }
};
