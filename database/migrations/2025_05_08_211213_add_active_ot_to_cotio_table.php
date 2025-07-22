<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotio', function (Blueprint $table) {
            $table->boolean('active_ot')->default(false)->after('enable_muestreo'); 
        });
    }

    public function down(): void
    {
        Schema::table('cotio', function (Blueprint $table) {
            $table->dropColumn('active_ot');
        });
    }
};
