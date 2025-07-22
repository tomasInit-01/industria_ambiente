<?php

namespace App\Services;

use App\Models\InventarioLab;
use App\Models\SimpleNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CalibracionNotificationService
{
    /**
     * Verifica equipos próximos a calibración y genera notificaciones
     */
    public function verificarCalibracionesProximas(): array
    {
        $resultado = [
            'equipos_encontrados' => 0,
            'notificaciones_creadas' => 0,
            'coordinadores_notificados' => 0,
            'errores' => []
        ];

        try {
            // Obtener equipos que necesitan calibración en las próximas 24 horas
            $equiposProximosCalibracion = InventarioLab::where('activo', true)
                ->whereNotNull('fecha_calibracion')
                ->where('fecha_calibracion', '<=', Carbon::now()->addDay())
                ->where('fecha_calibracion', '>', Carbon::now())
                ->get();

            $resultado['equipos_encontrados'] = $equiposProximosCalibracion->count();

            if ($equiposProximosCalibracion->isEmpty()) {
                return $resultado;
            }

            // Obtener coordinadores del sistema
            $coordinadores = $this->obtenerCoordinadores();
            
            if ($coordinadores->isEmpty()) {
                $resultado['errores'][] = 'No se encontraron coordinadores en el sistema';
                return $resultado;
            }

            $resultado['coordinadores_notificados'] = $coordinadores->count();

            foreach ($equiposProximosCalibracion as $equipo) {
                $fechaCalibracion = Carbon::parse($equipo->fecha_calibracion);
                $diasRestantes = Carbon::now()->diffInDays($fechaCalibracion, false);

                // Notificar si falta 1 día o menos
                if ($diasRestantes <= 1 && $diasRestantes >= 0) {
                    $mensaje = $this->generarMensajeCalibracion($equipo, $fechaCalibracion);
                    
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

        } catch (\Exception $e) {
            $resultado['errores'][] = "Error al verificar calibraciones: " . $e->getMessage();
            Log::error("Error en servicio de notificaciones de calibración: " . $e->getMessage());
        }

        return $resultado;
    }

    /**
     * Verifica equipos con calibración vencida
     */
    public function verificarCalibracionesVencidas(): array
    {
        $resultado = [
            'equipos_vencidos' => 0,
            'equipos' => []
        ];

        try {
            $equiposVencidos = InventarioLab::where('activo', true)
                ->whereNotNull('fecha_calibracion')
                ->where('fecha_calibracion', '<', Carbon::now())
                ->get();

            $resultado['equipos_vencidos'] = $equiposVencidos->count();

            foreach ($equiposVencidos as $equipo) {
                $resultado['equipos'][] = [
                    'id' => $equipo->id,
                    'equipamiento' => $equipo->equipamiento,
                    'marca_modelo' => $equipo->marca_modelo,
                    'n_serie_lote' => $equipo->n_serie_lote,
                    'fecha_calibracion' => $equipo->fecha_calibracion,
                    'dias_vencida' => Carbon::now()->diffInDays($equipo->fecha_calibracion)
                ];
            }

        } catch (\Exception $e) {
            Log::error("Error al verificar calibraciones vencidas: " . $e->getMessage());
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
    private function generarMensajeCalibracion(InventarioLab $equipo, Carbon $fechaCalibracion): string
    {
        $diasRestantes = Carbon::now()->diffInDays($fechaCalibracion, false);
        
        $tiempoTexto = "{$diasRestantes} día" . ($diasRestantes == 1 ? '' : 's');
        
        return "⚠️ EQUIPO REQUIERE CALIBRACIÓN: {$equipo->equipamiento} ({$equipo->marca_modelo}) - Serie: {$equipo->n_serie_lote} - Fecha de calibración: {$fechaCalibracion->format('d/m/Y')} - Faltan {$tiempoTexto}";
    }

    /**
     * Crea notificaciones para todos los coordinadores
     */
    private function crearNotificacionesParaCoordinadores($coordinadores, string $mensaje): int
    {
        $notificacionesCreadas = 0;

        foreach ($coordinadores as $coordinador) {
            // Verificar si ya existe una notificación similar para evitar duplicados
            $notificacionExistente = SimpleNotification::where('coordinador_codigo', $coordinador->usu_codigo)
                ->where('mensaje', $mensaje)
                ->where('created_at', '>=', Carbon::now()->subHours(2))
                ->first();

            if (!$notificacionExistente) {
                try {
                    SimpleNotification::create([
                        'coordinador_codigo' => $coordinador->usu_codigo,
                        'sender_codigo' => 'SISTEMA',
                        'instancia_id' => null,
                        'mensaje' => $mensaje,
                        'leida' => false
                    ]);
                    
                    $notificacionesCreadas++;
                } catch (\Exception $e) {
                    Log::error("Error al crear notificación para coordinador {$coordinador->usu_codigo}: " . $e->getMessage());
                }
            }
        }

        return $notificacionesCreadas;
    }

    /**
     * Obtiene estadísticas de calibración
     */
    public function obtenerEstadisticasCalibracion(): array
    {
        $hoy = Carbon::now();
        
        return [
            'total_equipos' => InventarioLab::where('activo', true)->count(),
            'equipos_con_calibracion' => InventarioLab::where('activo', true)
                ->whereNotNull('fecha_calibracion')
                ->count(),
            'proximos_24h' => InventarioLab::where('activo', true)
                ->whereNotNull('fecha_calibracion')
                ->where('fecha_calibracion', '<=', $hoy->addDay())
                ->where('fecha_calibracion', '>', Carbon::now())
                ->count(),
            'proximos_7dias' => InventarioLab::where('activo', true)
                ->whereNotNull('fecha_calibracion')
                ->where('fecha_calibracion', '<=', $hoy->addDays(7))
                ->where('fecha_calibracion', '>', Carbon::now())
                ->count(),
            'vencidos' => InventarioLab::where('activo', true)
                ->whereNotNull('fecha_calibracion')
                ->where('fecha_calibracion', '<', Carbon::now())
                ->count()
        ];
    }
} 