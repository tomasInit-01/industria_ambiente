<?php

namespace App\Console\Commands;

use App\Services\VencimientoMuestrasService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerifcarMuestrasVencidas extends Command
{
    protected $signature = 'app:verificar-muestras-vencidas';
    protected $description = 'Verifica las muestras a punto de vencer y genera notificaciones 24 horas antes';

    public function __construct(
        private VencimientoMuestrasService $vencimientoMuestrasService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Iniciando verificación de muestras a punto de vencer...');
        
        try {
            $resultado = $this->vencimientoMuestrasService->verificarMuestrasVencidas();
            
            $this->info("Encontrados {$resultado['muestras_encontradas']} muestras a punto de vencer");
            $this->info("Se crearon {$resultado['notificaciones_creadas']} notificaciones para {$resultado['coordinadores_notificados']} coordinadores");
            
            if (!empty($resultado['errores'])) {
                foreach ($resultado['errores'] as $error) {
                    $this->warn("Error: {$error}");
                }
            }

            $muestrasVencidas = $this->vencimientoMuestrasService->verificarCalibracionesVencidas();
            if($muestrasVencidas['muestras_vencidas'] > 0) {
                $this->warn("⚠️  Se encontraron {$muestrasVencidas['muestras_vencidas']} muestras vencidas:");
                foreach ($muestrasVencidas['muestras'] as $muestra) {
                    $this->warn("- {$muestra['cotio_descripcion']} ({$muestra['instance_number']}) - Vencida hace {$muestra['dias_vencida']} días");
                }
            }

            $this->mostrarEstadisticas();

            Log::info("Comando de verificación de muestras a punto de vencer ejecutado. Notificaciones creadas: {$resultado['notificaciones_creadas']}");

            return 0;

        } catch (\Exception $e) {
            $this->error("Error al verificar muestras: " . $e->getMessage());
            Log::error("Error en comando de verificación de muestras: " . $e->getMessage());
            return 1;
        }
    }

    private function mostrarEstadisticas(): void
    {
        $stats = $this->vencimientoMuestrasService->obtenerEstadisticasMuestras();
        
        $this->newLine();
        $this->info('📊 ESTADÍSTICAS DE MUESTRAS:');
        $this->line("Total de muestras encontradas: {$stats['muestras_encontradas']}");
        $this->line("Muestras a punto de vencer: {$stats['muestras_proximas_a_vencer']}");
        $this->line("Muestras vencidas: {$stats['muestras_vencidas']}");
    }
}