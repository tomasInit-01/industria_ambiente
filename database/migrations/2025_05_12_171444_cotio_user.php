<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cotio_user', function (Blueprint $table) {
            $table->integer('cotio_numcoti'); 
            $table->smallInteger('cotio_item'); 
            $table->smallInteger('cotio_subitem'); 
            $table->string('usu_codigo', 20); 

            
            $table->primary(['cotio_numcoti', 'cotio_item', 'cotio_subitem', 'usu_codigo']);

            
            $table->foreign(['cotio_numcoti', 'cotio_item', 'cotio_subitem'])
                  ->references(['cotio_numcoti', 'cotio_item', 'cotio_subitem'])
                  ->on('cotio')
                  ->onDelete('cascade');

            
            $table->foreign('usu_codigo')->references('usu_codigo')->on('usu');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cotio_user');
    }
};
