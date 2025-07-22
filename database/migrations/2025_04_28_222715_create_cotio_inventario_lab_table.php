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
        Schema::create('cotio_inventario_lab', function (Blueprint $table) {
            $table->unsignedInteger('cotio_numcoti');
            $table->unsignedInteger('cotio_item');
            $table->unsignedInteger('cotio_subitem');
            $table->unsignedBigInteger('inventario_lab_id');
            $table->primary(['cotio_numcoti', 'cotio_item', 'cotio_subitem', 'inventario_lab_id']);
            $table->foreign('inventario_lab_id')
                  ->references('id')
                  ->on('inventario_lab')
                  ->onDelete('cascade');
        
            $table->integer('cantidad')->nullable();
            $table->text('observaciones')->nullable();
        });
        
        
    }

    public function down(): void
    {
        Schema::dropIfExists('cotio_inventario_lab');
    }
};
