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
            $table->string('coti_referencia_tipo', 30)->nullable()->after('coti_mail1');
            $table->string('coti_referencia_valor', 120)->nullable()->after('coti_referencia_tipo');
            $table->string('coti_oc_referencia', 120)->nullable()->after('coti_referencia_valor');
            $table->string('coti_hes_has_tipo', 10)->nullable()->after('coti_oc_referencia');
            $table->string('coti_hes_has_valor', 120)->nullable()->after('coti_hes_has_tipo');
            $table->string('coti_gr_contrato', 120)->nullable()->after('coti_hes_has_valor');
            $table->string('coti_otro_referencia', 120)->nullable()->after('coti_gr_contrato');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coti', function (Blueprint $table) {
            $table->dropColumn([
                'coti_referencia_tipo',
                'coti_referencia_valor',
                'coti_oc_referencia',
                'coti_hes_has_tipo',
                'coti_hes_has_valor',
                'coti_gr_contrato',
                'coti_otro_referencia',
            ]);
        });
    }
};

