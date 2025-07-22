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
        Schema::table('simple_notifications', function (Blueprint $table) {
            $table->string('sender_codigo', 20)->nullable()->after('coordinador_codigo');
        });
    }

    public function down()
    {
        Schema::table('simple_notifications', function (Blueprint $table) {
            $table->dropColumn('sender_codigo');
        });
    }
};
