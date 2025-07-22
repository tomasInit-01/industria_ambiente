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
            $table->string('resultado', 50)
                  ->nullable()
                  ->after('cotio_estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('cotio', function (Blueprint $table) {
            $table->dropColumn('resultado');
        });
    }
};