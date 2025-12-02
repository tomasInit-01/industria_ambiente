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
            // Eliminar columnas obsoletas de la tabla coti
            $table->dropColumn([
                'coti_solensayo',
                'coti_remito',
                'coti_importe',
                'coti_sector',
                'coti_codigopag',
                'coti_usos',
                'coti_codigodiv',
                'coti_paridad',
                'coti_codigolp',
                'coti_nroprecio',
                'coti_vigencia',
                'coti_factor',
                'coti_interes',
                'coti_iva',
                'coti_impint',
                'coti_perciva',
                'coti_iibb',
                'coti_ganancias',
                'coti_acre',
                'coti_dto1',
                'coti_dto2',
                'coti_mail2',
                'coti_mail3',
                'coti_id',
                'coti_nrooc',
                'coti_abono',
                'coti_codigoclif',
                'coti_codigosucf'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coti', function (Blueprint $table) {
            // Restaurar columnas eliminadas
            $table->char('coti_solensayo', 30)->nullable();
            $table->char('coti_remito', 20)->nullable();
            $table->decimal('coti_importe', 15, 4)->nullable();
            $table->char('coti_sector', 5)->nullable();
            $table->char('coti_codigopag', 5)->nullable();
            $table->integer('coti_usos')->nullable();
            $table->char('coti_codigodiv', 5)->nullable();
            $table->decimal('coti_paridad', 12, 5)->nullable()->default(1);
            $table->char('coti_codigolp', 5)->nullable();
            $table->smallInteger('coti_nroprecio')->nullable();
            $table->smallInteger('coti_vigencia')->nullable();
            $table->decimal('coti_factor', 5, 2)->nullable()->default(1);
            $table->decimal('coti_interes')->nullable();
            $table->decimal('coti_iva')->nullable();
            $table->decimal('coti_impint')->nullable();
            $table->decimal('coti_perciva')->nullable();
            $table->decimal('coti_iibb')->nullable();
            $table->decimal('coti_ganancias')->nullable();
            $table->decimal('coti_acre')->nullable();
            $table->decimal('coti_dto1')->nullable();
            $table->decimal('coti_dto2')->nullable();
            $table->string('coti_mail2', 50)->nullable();
            $table->string('coti_mail3', 50)->nullable();
            $table->integer('coti_id')->nullable();
            $table->string('coti_nrooc', 50)->nullable();
            $table->boolean('coti_abono')->nullable()->default(false);
            $table->char('coti_codigoclif', 10)->nullable();
            $table->char('coti_codigosucf', 10)->nullable();
        });
    }
};
