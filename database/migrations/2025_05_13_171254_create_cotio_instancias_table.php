<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCotioInstanciasTable extends Migration
{
    public function up()
    {
        Schema::create('cotio_instancias', function (Blueprint $table) {
            $table->id();
            $table->integer('cotio_numcoti');
            $table->smallInteger('cotio_item');
            $table->smallInteger('cotio_subitem');
            $table->smallInteger('instance_number');
            $table->string('responsable_muestreo')->nullable();
            $table->timestamp('fecha_muestreo')->nullable();
            $table->string('resultado')->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('completado')->default(false);
            $table->timestamps();

            $table->unique([
                'cotio_numcoti', 
                'cotio_item', 
                'cotio_subitem', 
                'instance_number'
            ], 'cotio_instancia_unique');

            $table->foreign(['cotio_numcoti', 'cotio_item', 'cotio_subitem'])
                  ->references(['cotio_numcoti', 'cotio_item', 'cotio_subitem'])
                  ->on('cotio')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cotio_instancias');
    }
}
