<?php

namespace App\Console\Commands;

use App\Services\CalibracionNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerificarCalibracionInventarioLab extends Command
{
    protected $signature = 'inventario:verificar-calibracion';
    protected $description = 'Verifica las fechas de calibración del inventario de laboratorio y genera notificaciones 24 horas antes';

    public function __construct(
        private CalibracionNotificationService $calibracionService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Iniciando verificación de fechas de calibración...');
        
        try {
            // Verificar calibraciones próximas
            $resultado = $this->calibracionService->verificarCalibracionesProximas();
            
            $this->info("Encontrados {$resultado['equipos_encontrados']} equipos próximos a calibración");
            $this->info("Se crearon {$resultado['notificaciones_creadas']} notificaciones para {$resultado['coordinadores_notificados']} coordinadores");
            
            if (!empty($resultado['errores'])) {
                foreach ($resultado['errores'] as $error) {
                    $this->warn("Error: {$error}");
                }
            }
            
            // Verificar equipos con calibración vencida
            $equiposVencidos = $this->calibracionService->verificarCalibracionesVencidas();
            
            if ($equiposVencidos['equipos_vencidos'] > 0) {
                $this->warn("⚠️  Se encontraron {$equiposVencidos['equipos_vencidos']} equipos con calibración vencida:");
                
                foreach ($equiposVencidos['equipos'] as $equipo) {
                    $this->warn("- {$equipo['equipamiento']} ({$equipo['marca_modelo']}) - Vencida hace {$equipo['dias_vencida']} días");
                }
            }
            
            // Mostrar estadísticas
            $this->mostrarEstadisticas();
            
            Log::info("Comando de verificación de calibración ejecutado. Notificaciones creadas: {$resultado['notificaciones_creadas']}");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error al verificar calibraciones: " . $e->getMessage());
            Log::error("Error en comando de verificación de calibración: " . $e->getMessage());
            return 1;
        }
    }

    private function mostrarEstadisticas(): void
    {
        $stats = $this->calibracionService->obtenerEstadisticasCalibracion();
        
        $this->newLine();
        $this->info('📊 ESTADÍSTICAS DE CALIBRACIÓN:');
        $this->line("Total de equipos activos: {$stats['total_equipos']}");
        $this->line("Equipos con fecha de calibración: {$stats['equipos_con_calibracion']}");
        $this->line("Próximos a calibración (24h): {$stats['proximos_24h']}");
        $this->line("Próximos a calibración (7 días): {$stats['proximos_7dias']}");
        $this->line("Calibraciones vencidas: {$stats['vencidos']}");
    }
} 