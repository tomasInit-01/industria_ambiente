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
            // Agregar columna cli_partido si no existe
            if (!Schema::hasColumn('cli', 'cli_partido')) {
                $table->string('cli_partido', 50)->nullable();
            }
            
            // Agregar columnas de empresas relacionadas si no existen
            if (!Schema::hasColumn('cli', 'cli_rel_empresa_razon_social')) {
                $table->string('cli_rel_empresa_razon_social', 255)->nullable();
            }
            
            if (!Schema::hasColumn('cli', 'cli_rel_empresa_cuit')) {
                $table->string('cli_rel_empresa_cuit', 13)->nullable();
            }
            
            if (!Schema::hasColumn('cli', 'cli_rel_empresa_direcciones')) {
                $table->text('cli_rel_empresa_direcciones')->nullable();
            }
            
            if (!Schema::hasColumn('cli', 'cli_rel_empresa_localidad')) {
                $table->string('cli_rel_empresa_localidad', 50)->nullable();
            }
            
            if (!Schema::hasColumn('cli', 'cli_rel_empresa_partido')) {
                $table->string('cli_rel_empresa_partido', 50)->nullable();
            }
            
            if (!Schema::hasColumn('cli', 'cli_rel_empresa_contacto')) {
                $table->string('cli_rel_empresa_contacto', 100)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cli', function (Blueprint $table) {
            // Eliminar columnas de empresas relacionadas si existen
            if (Schema::hasColumn('cli', 'cli_rel_empresa_contacto')) {
                $table->dropColumn('cli_rel_empresa_contacto');
            }
            
            if (Schema::hasColumn('cli', 'cli_rel_empresa_partido')) {
                $table->dropColumn('cli_rel_empresa_partido');
            }
            
            if (Schema::hasColumn('cli', 'cli_rel_empresa_localidad')) {
                $table->dropColumn('cli_rel_empresa_localidad');
            }
            
            if (Schema::hasColumn('cli', 'cli_rel_empresa_direcciones')) {
                $table->dropColumn('cli_rel_empresa_direcciones');
            }
            
            if (Schema::hasColumn('cli', 'cli_rel_empresa_cuit')) {
                $table->dropColumn('cli_rel_empresa_cuit');
            }
            
            if (Schema::hasColumn('cli', 'cli_rel_empresa_razon_social')) {
                $table->dropColumn('cli_rel_empresa_razon_social');
            }
            
            // Eliminar columna cli_partido si existe
            if (Schema::hasColumn('cli', 'cli_partido')) {
                $table->dropColumn('cli_partido');
            }
        });
    }
};
