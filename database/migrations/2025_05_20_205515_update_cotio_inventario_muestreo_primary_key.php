<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateCotioInventarioMuestreoPrimaryKey extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up()
    {
        // Para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('
                ALTER TABLE cotio_inventario_muestreo 
                DROP CONSTRAINT IF EXISTS cotio_inventario_muestreo_pkey,
                ADD PRIMARY KEY (cotio_numcoti, cotio_item, cotio_subitem, instance_number, inventario_muestreo_id)
            ');
        }
        // Para MySQL
        else {
            DB::statement('
                ALTER TABLE cotio_inventario_muestreo 
                DROP PRIMARY KEY,
                ADD PRIMARY KEY (cotio_numcoti, cotio_item, cotio_subitem, instance_number, inventario_muestreo_id)
            ');
        }
    }

    /**
     * Revierte las migraciones.
     */
    public function down()
    {
        // Para PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('
                ALTER TABLE cotio_inventario_muestreo 
                DROP CONSTRAINT IF EXISTS cotio_inventario_muestreo_pkey,
                ADD PRIMARY KEY (cotio_numcoti, cotio_item, cotio_subitem, inventario_muestreo_id)
            ');
        }
        // Para MySQL
        else {
            DB::statement('
                ALTER TABLE cotio_inventario_muestreo 
                DROP PRIMARY KEY,
                ADD PRIMARY KEY (cotio_numcoti, cotio_item, cotio_subitem, inventario_muestreo_id)
            ');
        }
    }
}