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
            if (!Schema::hasColumn('coti', 'coti_sector')) {
                $table->char('coti_sector', 4)->nullable()->after('coti_mail1');
                $table->foreign('coti_sector')
                    ->references('divis_codigo')
                    ->on('divis')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coti', function (Blueprint $table) {
            if (Schema::hasColumn('coti', 'coti_sector')) {
                $table->dropForeign(['coti_sector']);
                $table->dropColumn('coti_sector');
            }
        });
    }
};

