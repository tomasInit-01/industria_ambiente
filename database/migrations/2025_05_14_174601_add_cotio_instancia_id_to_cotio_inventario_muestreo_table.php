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
        Schema::table('cotio_inventario_muestreo', function (Blueprint $table) {
            $table->unsignedBigInteger('cotio_instancia_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cotio_inventario_muestreo', function (Blueprint $table) {
            $table->dropForeign(['cotio_instancia_id']);
            $table->dropColumn('cotio_instancia_id');
        });
    }
};
