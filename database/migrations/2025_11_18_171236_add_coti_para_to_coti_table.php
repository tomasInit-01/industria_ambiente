<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('coti', function (Blueprint $table) {
            if (!Schema::hasColumn('coti', 'coti_para')) {
                $table->text('coti_para')->nullable()->after('coti_num');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coti', function (Blueprint $table) {
            if (Schema::hasColumn('coti', 'coti_para')) {
                $table->dropColumn('coti_para');
            }
        });
    }
};
