<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cli', function (Blueprint $table) {
            //
        });

        DB::statement("ALTER TABLE cli RENAME COLUMN cli_descuento1 TO cli_sector_laboratorio_pct");
        DB::statement("ALTER TABLE cli RENAME COLUMN cli_descuento2 TO cli_sector_higiene_pct");
        DB::statement("ALTER TABLE cli RENAME COLUMN cli_descuento3 TO cli_sector_microbiologia_pct");
        DB::statement("ALTER TABLE cli RENAME COLUMN cli_descuento4 TO cli_sector_cromatografia_pct");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cli', function (Blueprint $table) {
            //
        });

        DB::statement("ALTER TABLE cli RENAME COLUMN cli_sector_cromatografia_pct TO cli_descuento4");
        DB::statement("ALTER TABLE cli RENAME COLUMN cli_sector_microbiologia_pct TO cli_descuento3");
        DB::statement("ALTER TABLE cli RENAME COLUMN cli_sector_higiene_pct TO cli_descuento2");
        DB::statement("ALTER TABLE cli RENAME COLUMN cli_sector_laboratorio_pct TO cli_descuento1");
    }
};
