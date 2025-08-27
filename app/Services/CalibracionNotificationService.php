<?php

namespace App\Services;

use App\Models\InventarioLab;
use App\Models\InventarioMuestreo;
use App\Models\SimpleNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CalibracionNotificationService
{
    /**
     * Verifica equipos próximos a calibración en ambos inventarios
     */
    public function verificarCalibracionesProximas(): array
    {
        $resultado = [
            'equipos_lab_encontrados' => 0,
            'equipos_muestreo_encontrados' => 0,
            'notificaciones_creadas' => 0,
            'coordinadores_notificados' => 0,
            'errores' => []
        ];

        try {
            // Obtener coordinadores del sistema (una sola consulta)
            $coordinadores = $this->obtenerCoordinadores();
            
            if ($coordinadores->isEmpty()) {
                $resultado['errores'][] = 'No se encontraron coordinadores en el sistema';
                return $resultado;
            }

            $resultado['coordinadores_notificados'] = $coordinadores->count();

            // Procesar Inventario Lab
            $resultado = $this->procesarInventario(
                InventarioLab::class,
                $resultado,
                $coordinadores,
                'lab'
            );

            // Procesar Inventario Muestreo
            $resultado = $this->procesarInventario(
                InventarioMuestreo::class,
                $resultado,
                $coordinadores,
                'muestreo'
            );

        } catch (\Exception $e) {
            $resultado['errores'][] = "Error al verificar calibraciones: " . $e->getMessage();
            Log::error("Error en servicio de notificaciones de calibración: " . $e->getMessage());
        }

        return $resultado;
    }

    /**
     * Procesa un tipo de inventario para calibraciones próximas
     */
    private function procesarInventario(
        string $modelClass,
        array $resultado,
        $coordinadores,
        string $tipoInventario
    ): array {
        $equiposProximos = $modelClass::where('activo', true)
            ->whereNotNull('fecha_calibracion')
            ->where('fecha_calibracion', '<=', Carbon::now()->addDay())
            ->where('fecha_calibracion', '>', Carbon::now())
            ->get();

        $countField = "equipos_{$tipoInventario}_encontrados";
        $resultado[$countField] = $equiposProximos->count();

        foreach ($equiposProximos as $equipo) {
            $fechaCalibracion = Carbon::parse($equipo->fecha_calibracion);
            $diasRestantes = Carbon::now()->diffInDays($fechaCalibracion, false);

            if ($diasRestantes <= 1 && $diasRestantes >= 0) {
                $mensaje = $this->generarMensajeCalibracion($equipo, $fechaCalibracion, $tipoInventario);
                
                $notificacionesCreadas = $this->crearNotificacionesParaCoordinadores(
                    $coordinadores, 
                    $mensaje
                );
                
                // Desactivar el equipo después de notificar
                $equipo->activo = false;
                $equipo->save();
                
                $resultado['notificaciones_creadas'] += $notificacionesCreadas;
            }
        }

        return $resultado;
    }

    /**
     * Verifica equipos con calibración vencida en ambos inventarios
     */
    public function verificarCalibracionesVencidas(): array
    {
        $resultado = [
            'equipos_lab_vencidos' => 0,
            'equipos_muestreo_vencidos' => 0,
            'notificaciones_creadas' => 0,
            'equipos_lab' => [],
            'equipos_muestreo' => [],
            'equipos_desactivados' => 0
        ];
    
        try {
            $coordinadores = $this->obtenerCoordinadores();
            
            if ($coordinadores->isEmpty()) {
                throw new \Exception('No se encontraron coordinadores en el sistema');
            }
    
            // Procesar Inventario Lab
            $equiposVencidosLab = InventarioLab::where('activo', true)
                ->whereNotNull('fecha_calibracion')
                ->whereDate('fecha_calibracion', '<', Carbon::today())
                ->get();
            
            $resultado['equipos_lab_vencidos'] = $equiposVencidosLab->count();
            
            foreach ($equiposVencidosLab as $equipo) {
                $diasVencida = Carbon::now()->diffInDays(Carbon::parse($equipo->fecha_calibracion));
                
                // Desactivar el equipo
                $equipo->activo = false;
                $equipo->save();
                $resultado['equipos_desactivados']++;
                
                $resultado['equipos_lab'][] = [
                    'id' => $equipo->id,
                    'equipamiento' => $equipo->equipamiento,
                    'marca_modelo' => $equipo->marca_modelo ?? 'Sin marca/modelo',
                    'n_serie_lote' => $equipo->n_serie_lote,
                    'fecha_calibracion' => $equipo->fecha_calibracion,
                    'dias_vencida' => $diasVencida
                ];
                
                // Generar notificación
                $mensaje = "‼️ EQUIPO CON CALIBRACIÓN VENCIDA (LAB): {$equipo->equipamiento} - ".
                          ($equipo->marca_modelo ? "({$equipo->marca_modelo}) " : "").
                          "Vencido hace {$diasVencida} días - EQUIPO DESACTIVADO";
                
                $notificaciones = $this->crearNotificacionesParaCoordinadores($coordinadores, $mensaje);
                $resultado['notificaciones_creadas'] += $notificaciones;
                
                Log::info("Equipo desactivado y notificado (Lab): {$equipo->equipamiento}");
            }
    
            // Procesar Inventario Muestreo
            $equiposVencidosMuestreo = InventarioMuestreo::where('activo', true)
                ->whereNotNull('fecha_calibracion')
                ->whereDate('fecha_calibracion', '<', Carbon::today())
                ->get();
            
            $resultado['equipos_muestreo_vencidos'] = $equiposVencidosMuestreo->count();
            
            foreach ($equiposVencidosMuestreo as $equipo) {
                $diasVencida = Carbon::now()->diffInDays(Carbon::parse($equipo->fecha_calibracion));
                
                // Desactivar el equipo
                $equipo->activo = false;
                $equipo->save();
                $resultado['equipos_desactivados']++;
                
                $resultado['equipos_muestreo'][] = [
                    'id' => $equipo->id,
                    'equipamiento' => $equipo->equipamiento,
                    'marca_modelo' => $equipo->marca_modelo ?? 'Sin marca/modelo',
                    'n_serie_lote' => $equipo->n_serie_lote,
                    'fecha_calibracion' => $equipo->fecha_calibracion,
                    'dias_vencida' => $diasVencida
                ];
                
                $mensaje = "‼️ EQUIPO CON CALIBRACIÓN VENCIDA (MUESTREO): {$equipo->equipamiento} - ".
                          ($equipo->marca_modelo ? "({$equipo->marca_modelo}) " : "").
                          "Vencido hace {$diasVencida} días - EQUIPO DESACTIVADO";
                
                $notificaciones = $this->crearNotificacionesParaCoordinadores($coordinadores, $mensaje);
                $resultado['notificaciones_creadas'] += $notificaciones;
                
                Log::info("Equipo desactivado y notificado (Muestreo): {$equipo->equipamiento}");
            }
    
        } catch (\Exception $e) {
            Log::error("Error al verificar calibraciones vencidas: ".$e->getMessage());
            throw $e;
        }
    
        return $resultado;
    }
    /**
     * Procesa equipos vencidos para un tipo de inventario
     */
    private function procesarVencidos(
        string $modelClass,
        array $resultado,
        string $tipoInventario
    ): array {
        $equiposVencidos = $modelClass::where('activo', true)
            ->whereNotNull('fecha_calibracion')
            ->where('fecha_calibracion', '<', Carbon::now())
            ->get();

        $countField = "equipos_{$tipoInventario}_vencidos";
        $listField = "equipos_{$tipoInventario}";
        
        $resultado[$countField] = $equiposVencidos->count();

        foreach ($equiposVencidos as $equipo) {
            $resultado[$listField][] = [
                'id' => $equipo->id,
                'equipamiento' => $equipo->equipamiento,
                'marca_modelo' => $equipo->marca_modelo,
                'n_serie_lote' => $equipo->n_serie_lote,
                'fecha_calibracion' => $equipo->fecha_calibracion,
                'dias_vencida' => Carbon::now()->diffInDays($equipo->fecha_calibracion)
            ];
        }

        return $resultado;
    }

    /**
     * Obtiene los coordinadores del sistema
     */
    private function obtenerCoordinadores()
    {
        return User::where('rol', 'coordinador_lab')
            ->orWhere('usu_nivel', '>=' , 900)
            ->get();
    }

    /**
     * Genera el mensaje de notificación para un equipo
     */
    private function generarMensajeCalibracion($equipo, Carbon $fechaCalibracion, string $tipoInventario): string
    {
        $diasRestantes = Carbon::now()->diffInDays($fechaCalibracion, false);
        $tiempoTexto = "{$diasRestantes} día" . ($diasRestantes == 1 ? '' : 's');
        $tipoTexto = $tipoInventario === 'lab' ? 'LABORATORIO' : 'MUESTREO';
        
        return "⚠️ [{$tipoTexto}] EQUIPO REQUIERE CALIBRACIÓN: {$equipo->equipamiento} ({$equipo->marca_modelo}) - Serie: {$equipo->n_serie_lote} - Fecha de calibración: {$fechaCalibracion->format('d/m/Y')} - Faltan {$tiempoTexto}";
    }

    /**
     * Crea notificaciones para todos los coordinadores
     */
    private function crearNotificacionesParaCoordinadores($coordinadores, string $mensaje): int
    {
        $notificacionesCreadas = 0;
    
        foreach ($coordinadores as $coordinador) {
            try {
                // Verificar si ya existe una notificación idéntica en las últimas 24 horas
                $existeNotificacion = SimpleNotification::where('coordinador_codigo', $coordinador->usu_codigo)
                    ->where('mensaje', $mensaje)
                    ->where('created_at', '>', Carbon::now()->subDay())
                    ->exists();
    
                if (!$existeNotificacion) {
                    SimpleNotification::create([
                        'coordinador_codigo' => $coordinador->usu_codigo,
                        'sender_codigo' => 'SISTEMA',
                        'instancia_id' => null,
                        'mensaje' => $mensaje,
                        'url' => null, // Las notificaciones de calibración no tienen URL específica por ahora
                        'leida' => false,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    $notificacionesCreadas++;
                    Log::debug("Notificación creada para {$coordinador->usu_codigo}: {$mensaje}");
                }
            } catch (\Exception $e) {
                Log::error("Error al crear notificación para {$coordinador->usu_codigo}: ".$e->getMessage());
            }
        }
    
        Log::info("Total notificaciones creadas: {$notificacionesCreadas} para mensaje: {$mensaje}");
        return $notificacionesCreadas;
    }

    /**
     * Obtiene estadísticas de calibración para ambos inventarios
     */
    public function obtenerEstadisticasCalibracion(): array
    {
        $hoy = Carbon::now();
        
        $statsLab = $this->obtenerEstadisticasPorModelo(InventarioLab::class);
        $statsMuestreo = $this->obtenerEstadisticasPorModelo(InventarioMuestreo::class);
        
        return [
            'laboratorio' => $statsLab,
            'muestreo' => $statsMuestreo,
            'total' => [
                'equipos' => $statsLab['total_equipos'] + $statsMuestreo['total_equipos'],
                'con_calibracion' => $statsLab['equipos_con_calibracion'] + $statsMuestreo['equipos_con_calibracion'],
                'proximos_24h' => $statsLab['proximos_24h'] + $statsMuestreo['proximos_24h'],
                'proximos_7dias' => $statsLab['proximos_7dias'] + $statsMuestreo['proximos_7dias'],
                'vencidos' => $statsLab['vencidos'] + $statsMuestreo['vencidos']
            ]
        ];
    }

    /**
     * Obtiene estadísticas para un modelo específico
     */
    private function obtenerEstadisticasPorModelo(string $modelClass): array
    {
        $hoy = Carbon::now();
        
        return [
            'total_equipos' => $modelClass::where('activo', true)->count(),
            'equipos_con_calibracion' => $modelClass::where('activo', true)
                ->whereNotNull('fecha_calibracion')
                ->count(),
            'proximos_24h' => $modelClass::where('activo', true)
                ->whereNotNull('fecha_calibracion')
                ->where('fecha_calibracion', '<=', $hoy->addDay())
                ->where('fecha_calibracion', '>', Carbon::now())
                ->count(),
            'proximos_7dias' => $modelClass::where('activo', true)
                ->whereNotNull('fecha_calibracion')
                ->where('fecha_calibracion', '<=', $hoy->addDays(7))
                ->where('fecha_calibracion', '>', Carbon::now())
                ->count(),
            'vencidos' => $modelClass::where('activo', true)
                ->whereNotNull('fecha_calibracion')
                ->where('fecha_calibracion', '<', Carbon::now())
                ->count()
        ];
    }
}