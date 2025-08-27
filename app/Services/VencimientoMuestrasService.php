<?php

namespace App\Services;

use App\Models\CotioInstancia;
use App\Models\SimpleNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class VencimientoMuestrasService {

    public function verificarMuestrasVencidas() {
        $resultado = [
            'muestras_encontradas' => 0,
            'muestras_vencidas' => 0,
            'coordinadores_notificados' => 0,
            'notificaciones_creadas' => 0,
            'errores' => []
        ];

        try {
            // Obtener muestras que necesitan calibración en las próximas 24 horas
            $muestrasProximasVencimiento = CotioInstancia::where('cotio_subitem', 0)
                ->where('cotio_estado', '!=', 'muestreado')
                ->where('fecha_fin_muestreo', '<=', Carbon::now()->addDay())
                ->where('fecha_fin_muestreo', '>', Carbon::now())
                ->get();

            $resultado['muestras_encontradas'] = $muestrasProximasVencimiento->count();

            if ($muestrasProximasVencimiento->isEmpty()) {
                return $resultado;
            }

            // Obtener coordinadores del sistema
            $coordinadores = $this->obtenerCoordinadores();

            if ($coordinadores->isEmpty()) {
                $resultado['errores'][] = 'No se encontraron coordinadores en el sistema';
                return $resultado;
            }

            $resultado['coordinadores_notificados'] = $coordinadores->count();

            foreach ($muestrasProximasVencimiento as $muestra) {
                $fechaVencimiento = Carbon::parse($muestra->fecha_fin_muestreo);
                $diasRestantes = Carbon::now()->diffInDays($fechaVencimiento, false);
                
                if ($diasRestantes <= 1 && $diasRestantes >= 0) {
                    $mensaje = $this->generarMensajeVencimientoMuestras($muestra, $fechaVencimiento);
                    
                    $notificacionesCreadas = $this->crearNotificacionesParaCoordinadores(
                        $coordinadores, 
                        $mensaje,
                        $muestra
                    );
                    
                    $resultado['notificaciones_creadas'] += $notificacionesCreadas;
                }
            }


        } catch (\Exception $e) {
            $resultado['errores'][] = "Error al verificar muestras: " . $e->getMessage();
            Log::error("Error al verificar muestras: " . $e->getMessage());
            return $resultado;
        }

        return $resultado;
    }


    private function obtenerCoordinadores()
    {
        return User::where('rol', 'coordinador_muestreo')
            ->orWhere('usu_nivel', '>=' , 900)
            ->get();
    }


    public function verificarCalibracionesVencidas(): array
    {
        $resultado = [
            'muestras_vencidas' => 0,
            'muestras' => []
        ];

        try {
            $muestrasVencidas = CotioInstancia::where('cotio_subitem', 0)
                ->where('cotio_estado', '!=', 'muestreado')
                ->where('fecha_fin_muestreo', '<', Carbon::now())
                ->get();

            $resultado['muestras_vencidas'] = $muestrasVencidas->count();

            foreach ($muestrasVencidas as $muestra) {
                $resultado['muestras'][] = [
                    'id' => $muestra->id,
                    'cotio_descripcion' => $muestra->cotio_descripcion,
                    'fecha_fin_muestreo' => $muestra->fecha_fin_muestreo,
                    'instance_number' => $muestra->instance_number,
                    'dias_vencida' => Carbon::now()->diffInDays($muestra->fecha_fin_muestreo)
                ];
            }

        } catch (\Exception $e) {
            Log::error("Error al verificar calibraciones vencidas: " . $e->getMessage());
        }

        return $resultado;
    }



    private function generarMensajeVencimientoMuestras(CotioInstancia $muestra, Carbon $fechaVencimiento): string
    {
        return "⚠️ MUESTRA A PUNTO DE VENCER: {$muestra->cotio_descripcion} - Fecha de vencimiento: {$fechaVencimiento->format('d/m/Y')}";
    }


    private function crearNotificacionesParaCoordinadores($coordinadores, string $mensaje, $muestra = null): int
    {
        $notificacionesCreadas = 0;

        foreach ($coordinadores as $coordinador) {
            $notificacionExistente = SimpleNotification::where('coordinador_codigo', $coordinador->usu_codigo)
                ->where('mensaje', $mensaje)
                ->where('created_at', '>=', Carbon::now()->subHours(2))
                ->first();

            if (!$notificacionExistente) {
                SimpleNotification::create([
                    'coordinador_codigo' => $coordinador->usu_codigo,
                    'sender_codigo' => 'SISTEMA',
                    'instancia_id' => $muestra ? $muestra->id : null,
                    'mensaje' => $mensaje,
                    'url' => $muestra ? SimpleNotification::generarUrlPorRol($coordinador->usu_codigo, $muestra->id) : null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
                $notificacionesCreadas++;
            }
        }

        return $notificacionesCreadas;
    }


    public function obtenerEstadisticasMuestras(): array
    {
        $hoy = Carbon::now();

        return [
            'muestras_encontradas' => CotioInstancia::where('cotio_subitem', 0)
                ->where('cotio_estado', '!=', 'muestreado')
                ->where('fecha_fin_muestreo', '<=', $hoy->addDay())
                ->where('fecha_fin_muestreo', '>', $hoy)
                ->count(),
            'muestras_proximas_a_vencer' => CotioInstancia::where('cotio_subitem', 0)
                ->where('cotio_estado', '!=', 'muestreado')
                ->where('fecha_fin_muestreo', '<=', $hoy->addDay())
                ->where('fecha_fin_muestreo', '>', $hoy)
                ->count(),
            'muestras_vencidas' => CotioInstancia::where('cotio_subitem', 0)
                ->where('cotio_estado', '!=', 'muestreado')
                ->where('fecha_fin_muestreo', '<', $hoy)
                ->count()
        ];
    }
}