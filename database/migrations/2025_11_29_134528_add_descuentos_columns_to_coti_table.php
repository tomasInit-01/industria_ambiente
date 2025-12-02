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
            // Descuento global
            $table->decimal('coti_descuentoglobal', 10, 2)->nullable()->default(0.00)->after('coti_mail1');
            
            // Descuentos por sector - porcentajes
            $table->decimal('coti_sector_laboratorio_pct', 10, 2)->nullable()->default(0.00)->after('coti_descuentoglobal');
            $table->decimal('coti_sector_higiene_pct', 10, 2)->nullable()->default(0.00)->after('coti_sector_laboratorio_pct');
            $table->decimal('coti_sector_microbiologia_pct', 10, 2)->nullable()->default(0.00)->after('coti_sector_higiene_pct');
            $table->decimal('coti_sector_cromatografia_pct', 10, 2)->nullable()->default(0.00)->after('coti_sector_microbiologia_pct');
            
            // Contactos por sector
            $table->string('coti_sector_laboratorio_contacto', 100)->nullable()->after('coti_sector_cromatografia_pct');
            $table->string('coti_sector_higiene_contacto', 100)->nullable()->after('coti_sector_laboratorio_contacto');
            $table->string('coti_sector_microbiologia_contacto', 100)->nullable()->after('coti_sector_higiene_contacto');
            $table->string('coti_sector_cromatografia_contacto', 100)->nullable()->after('coti_sector_microbiologia_contacto');
            
            // Observaciones por sector
            $table->text('coti_sector_laboratorio_observaciones')->nullable()->after('coti_sector_cromatografia_contacto');
            $table->text('coti_sector_higiene_observaciones')->nullable()->after('coti_sector_laboratorio_observaciones');
            $table->text('coti_sector_microbiologia_observaciones')->nullable()->after('coti_sector_higiene_observaciones');
            $table->text('coti_sector_cromatografia_observaciones')->nullable()->after('coti_sector_microbiologia_observaciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coti', function (Blueprint $table) {
            $table->dropColumn([
                'coti_descuentoglobal',
                'coti_sector_laboratorio_pct',
                'coti_sector_higiene_pct',
                'coti_sector_microbiologia_pct',
                'coti_sector_cromatografia_pct',
                'coti_sector_laboratorio_contacto',
                'coti_sector_higiene_contacto',
                'coti_sector_microbiologia_contacto',
                'coti_sector_cromatografia_contacto',
                'coti_sector_laboratorio_observaciones',
                'coti_sector_higiene_observaciones',
                'coti_sector_microbiologia_observaciones',
                'coti_sector_cromatografia_observaciones'
            ]);
        });
    }
};
