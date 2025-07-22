<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class ReiniciarCategoriasFrecuentes extends Command
{
    protected $signature = 'app:reiniciar-categorias-frecuentes';
    protected $description = 'Reinicia categorías frecuentes y actualiza el contador en formato "NOMBRE (X/Y)"';

    public function handle()
    {
        $output = [];
        $stats = [
            'categorias' => 0,
            'tareas' => 0,
            'herramientas' => 0,
            'vehiculos' => 0
        ];
    
        DB::transaction(function () use (&$output, &$stats) {
            $categorias = DB::table('cotio')
                ->where('es_frecuente', true)
                ->where('cotio_subitem', 0)
                ->where('cotio_estado', 'finalizado')
                ->whereNotNull('fecha_fin')
                ->where(function ($query) {
                    $query->orWhere(function ($q) {
                        $q->where('frecuencia_dias', 'diario')
                          ->where('fecha_fin', '<=', now()->subDay());
                    })->orWhere(function ($q) {
                        $q->where('frecuencia_dias', 'semanal')
                          ->where('fecha_fin', '<=', now()->subDays(7));
                    })->orWhere(function ($q) {
                        $q->where('frecuencia_dias', 'quincenal')
                          ->where('fecha_fin', '<=', now()->subDays(15));
                    })->orWhere(function ($q) {
                        $q->where('frecuencia_dias', 'mensual')
                          ->where('fecha_fin', '<=', now()->subMonth());
                    })->orWhere(function ($q) {
                        $q->where('frecuencia_dias', 'trimestral')
                          ->where('fecha_fin', '<=', now()->subMonths(3));
                    })->orWhere(function ($q) {
                        $q->where('frecuencia_dias', 'cuatr')
                          ->where('fecha_fin', '<=', now()->subMonths(4));
                    })->orWhere(function ($q) {
                        $q->where('frecuencia_dias', 'semestral')
                          ->where('fecha_fin', '<=', now()->subMonths(6));
                    })->orWhere(function ($q) {
                        $q->where('frecuencia_dias', 'anual')
                          ->where('fecha_fin', '<=', now()->subYear());
                    });
                })
                ->get(['cotio_numcoti', 'cotio_item', 'cotio_descripcion', 'cotio_cantidad', 'vehiculo_asignado', 'muestreo_contador']);
    
            foreach ($categorias as $categoria) {
                $stats['categorias']++;
                
                // 1. Calcular contador de reinicios
                $reinicios = is_numeric($categoria->muestreo_contador) 
                    ? (int)$categoria->muestreo_contador + 1 
                    : 1;
    
                // 2. Liberar vehículo si existe
                if ($categoria->vehiculo_asignado) {
                    $updated = DB::table('vehiculos')
                        ->where('id', $categoria->vehiculo_asignado)
                        ->where('estado', '!=', 'libre')
                        ->update(['estado' => 'libre']);
                    
                    if ($updated) $stats['vehiculos']++;
                }
    
                // 3. Actualizar categoría principal (sin modificar cotio_cantidad)
                DB::table('cotio')
                    ->where('cotio_numcoti', $categoria->cotio_numcoti)
                    ->where('cotio_item', $categoria->cotio_item)
                    ->where('cotio_subitem', 0)
                    ->update([
                        'cotio_estado' => 'pendiente',
                        'fecha_inicio' => null,
                        'fecha_fin' => null,
                        'cotio_identificacion' => null,
                        'cotio_responsable_codigo' => null,
                        'vehiculo_asignado' => null,
                        'muestreo_contador' => $reinicios // Solo actualizamos este campo
                    ]);
    
                // 4. Actualizar tareas relacionadas
                $tareasActualizadas = DB::table('cotio')
                    ->where('cotio_numcoti', $categoria->cotio_numcoti)
                    ->where('cotio_item', $categoria->cotio_item)
                    ->where('cotio_subitem', '>', 0)
                    ->update([
                        'cotio_estado' => 'pendiente',
                        'fecha_inicio' => null,
                        'fecha_fin' => null,
                        'cotio_identificacion' => null,
                        'cotio_responsable_codigo' => null,
                        'vehiculo_asignado' => null,
                        'resultado' => null
                    ]);
    
                $stats['tareas'] += $tareasActualizadas;
    
                // 5. Liberar herramientas si existen
                $herramientasIds = DB::table('cotio_inventario_lab')
                    ->where('cotio_numcoti', $categoria->cotio_numcoti)
                    ->where('cotio_item', $categoria->cotio_item)
                    ->pluck('inventario_lab_id');
    
                if ($herramientasIds->isNotEmpty()) {
                    DB::table('cotio_inventario_lab')
                        ->where('cotio_numcoti', $categoria->cotio_numcoti)
                        ->where('cotio_item', $categoria->cotio_item)
                        ->delete();
    
                    $herramientasActualizadas = DB::table('inventario_lab')
                        ->whereIn('id', $herramientasIds)
                        ->where('estado', '!=', 'libre')
                        ->update(['estado' => 'libre']);
    
                    $stats['herramientas'] += $herramientasActualizadas;
                }
    
                // Preparar output para mostrar (con formato visual)
                $output[] = [
                    'Categoría' => Str::limit($categoria->cotio_descripcion, 25),
                    'OT' => "OT-{$categoria->cotio_numcoti}-{$categoria->cotio_item}",
                    'Progreso' => "{$reinicios}/{$categoria->cotio_cantidad}",
                    'Tareas' => $tareasActualizadas,
                    'Herramientas' => $herramientasIds->count(),
                    'Vehículo' => $categoria->vehiculo_asignado ? 'Liberado' : 'N/A'
                ];
            }
        });
    
        // Mostrar resultados
        $this->info("=== RESUMEN DE EJECUCIÓN ===");
        $this->info(sprintf(
            "Categorías actualizadas: %d\nTareas reiniciadas: %d\nHerramientas liberadas: %d\nVehículos liberados: %d",
            $stats['categorias'],
            $stats['tareas'],
            $stats['herramientas'],
            $stats['vehiculos']
        ));
    
        if ($stats['categorias'] > 0) {
            $this->info("\n=== DETALLE POR CATEGORÍA ===");
            $this->table(
                array_keys($output[0]),
                array_map(function($item) {
                    return array_values($item);
                }, $output)
            );
        } else {
            $this->info("\nNo se encontraron categorías para actualizar.");
        }
    
        $this->info("\n✅ Proceso completado exitosamente a las " . now()->format('Y-m-d H:i:s'));
    }
}