<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->boolean('active_muestreo')->default(false);
            $table->boolean('active_ot')->default(false);
            $table->boolean('complete_muestreo')->default(false);
            $table->boolean('complete_ot')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('cotio_instancias', function (Blueprint $table) {
            $table->dropColumn([
                'active_muestreo',
                'active_ot',
                'complete_muestreo',
                'complete_ot',
            ]);
        });
    }
};

