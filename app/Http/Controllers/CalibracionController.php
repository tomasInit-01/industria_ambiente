<?php

namespace App\Http\Controllers;

use App\Models\InventarioLab;
use App\Models\SimpleNotification;
use App\Services\CalibracionNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalibracionController extends Controller
{
    public function __construct(
        private CalibracionNotificationService $calibracionService
    ) {}

    /**
     * Muestra el dashboard de calibraciones
     */
    public function index()
    {
        $stats = $this->calibracionService->obtenerEstadisticasCalibracion();
        
        // Obtener equipos próximos a calibración
        $equiposProximos = InventarioLab::where('activo', true)
            ->whereNotNull('fecha_calibracion')
            ->where('fecha_calibracion', '<=', Carbon::now()->addDays(7))
            ->where('fecha_calibracion', '>', Carbon::now())
            ->orderBy('fecha_calibracion')
            ->get();

        // Obtener equipos con calibración vencida
        $equiposVencidos = InventarioLab::where('activo', true)
            ->whereNotNull('fecha_calibracion')
            ->where('fecha_calibracion', '<', Carbon::now())
            ->orderBy('fecha_calibracion')
            ->get();

        // Obtener notificaciones recientes de calibración
        $notificaciones = SimpleNotification::where('mensaje', 'like', '%EQUIPO REQUIERE CALIBRACIÓN%')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->with('coordinador')
            ->get();

        return view('calibracion.index', compact(
            'stats',
            'equiposProximos',
            'equiposVencidos',
            'notificaciones'
        ));
    }

    /**
     * Ejecuta manualmente la verificación de calibraciones
     */
    public function ejecutarVerificacion()
    {
        try {
            $resultado = $this->calibracionService->verificarCalibracionesProximas();
            
            return response()->json([
                'success' => true,
                'message' => 'Verificación ejecutada exitosamente',
                'data' => $resultado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al ejecutar verificación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene las estadísticas de calibración
     */
    public function estadisticas()
    {
        $stats = $this->calibracionService->obtenerEstadisticasCalibracion();
        
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Marca una notificación como leída
     */
    public function marcarLeida(Request $request, $id)
    {
        $notificacion = SimpleNotification::findOrFail($id);
        
        // Verificar que el usuario actual es el destinatario
        if ($notificacion->coordinador_codigo !== Auth::user()->usu_codigo) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para marcar esta notificación'
            ], 403);
        }

        $notificacion->update(['leida' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notificación marcada como leída'
        ]);
    }

    /**
     * Obtiene equipos próximos a calibración
     */
    public function equiposProximos()
    {
        $equipos = InventarioLab::where('activo', true)
            ->whereNotNull('fecha_calibracion')
            ->where('fecha_calibracion', '<=', Carbon::now()->addDays(7))
            ->where('fecha_calibracion', '>', Carbon::now())
            ->orderBy('fecha_calibracion')
            ->get()
            ->map(function ($equipo) {
                $fechaCalibracion = Carbon::parse($equipo->fecha_calibracion);
                $horasRestantes = Carbon::now()->diffInHours($fechaCalibracion, false);
                $diasRestantes = Carbon::now()->diffInDays($fechaCalibracion, false);
                
                return [
                    'id' => $equipo->id,
                    'equipamiento' => $equipo->equipamiento,
                    'marca_modelo' => $equipo->marca_modelo,
                    'n_serie_lote' => $equipo->n_serie_lote,
                    'fecha_calibracion' => $equipo->fecha_calibracion,
                    'horas_restantes' => $horasRestantes,
                    'dias_restantes' => $diasRestantes,
                    'urgente' => $horasRestantes <= 24,
                    'estado' => $equipo->estado
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $equipos
        ]);
    }

    /**
     * Obtiene equipos con calibración vencida
     */
    public function equiposVencidos()
    {
        $equipos = InventarioLab::where('activo', true)
            ->whereNotNull('fecha_calibracion')
            ->where('fecha_calibracion', '<', Carbon::now())
            ->orderBy('fecha_calibracion')
            ->get()
            ->map(function ($equipo) {
                $fechaCalibracion = Carbon::parse($equipo->fecha_calibracion);
                $diasVencida = Carbon::now()->diffInDays($equipo->fecha_calibracion);
                
                return [
                    'id' => $equipo->id,
                    'equipamiento' => $equipo->equipamiento,
                    'marca_modelo' => $equipo->marca_modelo,
                    'n_serie_lote' => $equipo->n_serie_lote,
                    'fecha_calibracion' => $equipo->fecha_calibracion,
                    'dias_vencida' => $diasVencida,
                    'estado' => $equipo->estado
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $equipos
        ]);
    }
} 