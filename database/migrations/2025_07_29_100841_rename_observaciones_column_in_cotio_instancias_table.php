<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->renameColumn('observaciones', 'observaciones_ot');
        });
    }

    public function down(): void
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->renameColumn('observaciones_ot', 'observaciones');
        });
    }
};
