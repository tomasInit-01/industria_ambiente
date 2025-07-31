<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClienteFieldsToFacturasTable extends Migration
{
    public function up()
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->string('cliente_razon_social', 100)->nullable()->after('cotizacion_id');
            $table->string('cliente_cuit', 20)->nullable()->after('cliente_razon_social');
        });
    }

    public function down()
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropColumn(['cliente_razon_social', 'cliente_cuit']);
        });
    }
}