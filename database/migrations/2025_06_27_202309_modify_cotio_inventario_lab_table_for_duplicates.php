<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ModifyCotioInventarioLabTableForDuplicates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Verificar si la tabla existe
        if (Schema::hasTable('cotio_inventario_lab')) {
            // 1. Crear tabla temporal para almacenar los datos existentes
            Schema::dropIfExists('temp_cotio_inventario_lab');
            
            DB::statement('CREATE TABLE temp_cotio_inventario_lab AS SELECT * FROM cotio_inventario_lab');
            
            // 2. Eliminar la tabla original
            Schema::drop('cotio_inventario_lab');
            
            // 3. Recrear la tabla con nueva estructura
            Schema::create('cotio_inventario_lab', function (Blueprint $table) {
                // Nuevo ID autoincremental como clave primaria
                $table->id();
                
                // Columnas originales
                $table->integer('cotio_numcoti');
                $table->integer('cotio_item');
                $table->integer('cotio_subitem');
                $table->foreignId('inventario_lab_id')->constrained('inventario_lab');
                $table->integer('cantidad')->nullable();
                $table->text('observaciones')->nullable();
                $table->integer('instance_number');
                $table->bigInteger('cotio_instancia_id')->nullable();
                
                // Timestamps para mejor control
                $table->timestamps();
                
                // Índices para mejorar el rendimiento
                $table->index(['cotio_numcoti', 'cotio_item', 'cotio_subitem', 'instance_number'], 'idx_cotio_relations');
                $table->index('inventario_lab_id');
                $table->index('cotio_instancia_id');
            });
            
            // 4. Migrar los datos de vuelta
            DB::table('temp_cotio_inventario_lab')->orderBy('ctid')->chunk(1000, function ($records) {
                $data = $records->map(function ($record) {
                    return [
                        'cotio_numcoti' => $record->cotio_numcoti,
                        'cotio_item' => $record->cotio_item,
                        'cotio_subitem' => $record->cotio_subitem,
                        'inventario_lab_id' => $record->inventario_lab_id,
                        'cantidad' => $record->cantidad,
                        'observaciones' => $record->observaciones,
                        'instance_number' => $record->instance_number,
                        'cotio_instancia_id' => $record->cotio_instancia_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();
                
                DB::table('cotio_inventario_lab')->insert($data);
            });
            
            // 5. Eliminar la tabla temporal
            Schema::drop('temp_cotio_inventario_lab');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revertir los cambios (versión simplificada)
        if (Schema::hasTable('cotio_inventario_lab')) {
            // Crear backup
            Schema::dropIfExists('backup_cotio_inventario_lab');
            DB::statement('CREATE TABLE backup_cotio_inventario_lab AS SELECT * FROM cotio_inventario_lab');
            
            // Eliminar la tabla modificada
            Schema::drop('cotio_inventario_lab');
            
            // Recrear la tabla original (sin el ID autoincremental)
            Schema::create('cotio_inventario_lab', function (Blueprint $table) {
                $table->integer('cotio_numcoti');
                $table->integer('cotio_item');
                $table->integer('cotio_subitem');
                $table->foreignId('inventario_lab_id')->constrained('inventario_lab');
                $table->integer('cantidad')->nullable();
                $table->text('observaciones')->nullable();
                $table->integer('instance_number');
                $table->bigInteger('cotio_instancia_id')->nullable();
                
                // Índices originales (ajustar según necesidad)
                $table->primary(['cotio_numcoti', 'cotio_item', 'cotio_subitem', 'inventario_lab_id', 'instance_number']);
            });
            
            // Migrar datos de vuelta (sin el ID)
            DB::table('backup_cotio_inventario_lab')->orderBy('id')->chunk(1000, function ($records) {
                $data = $records->map(function ($record) {
                    return [
                        'cotio_numcoti' => $record->cotio_numcoti,
                        'cotio_item' => $record->cotio_item,
                        'cotio_subitem' => $record->cotio_subitem,
                        'inventario_lab_id' => $record->inventario_lab_id,
                        'cantidad' => $record->cantidad,
                        'observaciones' => $record->observaciones,
                        'instance_number' => $record->instance_number,
                        'cotio_instancia_id' => $record->cotio_instancia_id,
                    ];
                })->toArray();
                
                DB::table('cotio_inventario_lab')->insert($data);
            });
            
            // Eliminar backup
            Schema::drop('backup_cotio_inventario_lab');
        }
    }
}