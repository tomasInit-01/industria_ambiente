<?php

namespace App\Console\Commands;

use App\Services\CalibracionNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerificarCalibracionInventarioLab extends Command
{
    protected $signature = 'inventario:verificar-calibracion';
    protected $description = 'Verifica las fechas de calibración del inventario de laboratorio y muestreo, generando notificaciones 24 horas antes';

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
            
            $this->info("Encontrados {$resultado['equipos_lab_encontrados']} equipos de laboratorio próximos a calibración");
            $this->info("Encontrados {$resultado['equipos_muestreo_encontrados']} equipos de muestreo próximos a calibración");
            $this->info("Se crearon {$resultado['notificaciones_creadas']} notificaciones para {$resultado['coordinadores_notificados']} coordinadores");
            
            if (!empty($resultado['errores'])) {
                foreach ($resultado['errores'] as $error) {
                    $this->warn("Error: {$error}");
                }
            }
            
            // Verificar equipos con calibración vencida
            $equiposVencidos = $this->calibracionService->verificarCalibracionesVencidas();
            
            if ($equiposVencidos['equipos_lab_vencidos'] > 0 || $equiposVencidos['equipos_muestreo_vencidos'] > 0) {
                $this->warn("⚠️  Se encontraron equipos con calibración vencida:");
                
                if ($equiposVencidos['equipos_lab_vencidos'] > 0) {
                    $this->warn("Laboratorio ({$equiposVencidos['equipos_lab_vencidos']}):");
                    foreach ($equiposVencidos['equipos_lab'] as $equipo) {
                        $marcaModelo = $equipo['marca_modelo'] ?? 'Sin marca/modelo';
                        $this->warn("- {$equipo['equipamiento']} ($marcaModelo) - Vencida hace {$equipo['dias_vencida']} días");
                    }
                }
                
                if ($equiposVencidos['equipos_muestreo_vencidos'] > 0) {
                    $this->warn("Muestreo ({$equiposVencidos['equipos_muestreo_vencidos']}):");
                    foreach ($equiposVencidos['equipos_muestreo'] as $equipo) {
                        $marcaModelo = $equipo['marca_modelo'] ?? 'Sin marca/modelo';
                        $this->warn("- {$equipo['equipamiento']} ($marcaModelo) - Vencida hace {$equipo['dias_vencida']} días");
                    }
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
        
        $this->line(PHP_EOL.'LABORATORIO:');
        $this->line("Total de equipos: {$stats['laboratorio']['total_equipos']}");
        $this->line("Con calibración: {$stats['laboratorio']['equipos_con_calibracion']}");
        $this->line("Próximos 24h: {$stats['laboratorio']['proximos_24h']}");
        $this->line("Próximos 7 días: {$stats['laboratorio']['proximos_7dias']}");
        $this->line("Vencidos: {$stats['laboratorio']['vencidos']}");
        
        $this->line(PHP_EOL.'MUESTREO:');
        $this->line("Total de equipos: {$stats['muestreo']['total_equipos']}");
        $this->line("Con calibración: {$stats['muestreo']['equipos_con_calibracion']}");
        $this->line("Próximos 24h: {$stats['muestreo']['proximos_24h']}");
        $this->line("Próximos 7 días: {$stats['muestreo']['proximos_7dias']}");
        $this->line("Vencidos: {$stats['muestreo']['vencidos']}");
        
        $this->line(PHP_EOL.'TOTALES:');
        $this->line("Total equipos: {$stats['total']['equipos']}");
        $this->line("Con calibración: {$stats['total']['con_calibracion']}");
        $this->line("Próximos 24h: {$stats['total']['proximos_24h']}");
        $this->line("Próximos 7 días: {$stats['total']['proximos_7dias']}");
        $this->line("Vencidos: {$stats['total']['vencidos']}");
    }
}