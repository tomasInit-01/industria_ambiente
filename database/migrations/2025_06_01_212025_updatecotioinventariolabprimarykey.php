<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCotioInventarioLabPrimaryKey extends Migration
{
    public function up()
    {
        // Remove existing primary key
        Schema::table('cotio_inventario_lab', function (Blueprint $table) {
            $table->dropPrimary('cotio_inventario_lab_pkey');
        });

        // Add instance_number to primary key
        Schema::table('cotio_inventario_lab', function (Blueprint $table) {
            $table->primary(
                ['cotio_numcoti', 'cotio_item', 'cotio_subitem', 'instance_number', 'inventario_lab_id'],
                'cotio_inventario_lab_pkey'
            );
        });
    }

    public function down()
    {
        // Revert to original primary key
        Schema::table('cotio_inventario_lab', function (Blueprint $table) {
            $table->dropPrimary('cotio_inventario_lab_pkey');
        });

        Schema::table('cotio_inventario_lab', function (Blueprint $table) {
            $table->primary(
                ['cotio_numcoti', 'cotio_item', 'cotio_subitem', 'inventario_lab_id'],
                'cotio_inventario_lab_pkey'
            );
        });
    }
}