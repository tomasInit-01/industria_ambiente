<?php

namespace App\Console\Commands;

use App\Models\InventarioLab;
use App\Services\CalibracionNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProbarNotificacionesCalibracion extends Command
{
    protected $signature = 'inventario:probar-notificaciones {--equipo_id= : ID específico del equipo para probar} {--forzar : Forzar notificación sin verificar duplicados}';
    protected $description = 'Prueba el sistema de notificaciones de calibración del inventario de laboratorio';

    public function __construct(
        private CalibracionNotificationService $calibracionService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🧪 PROBANDO SISTEMA DE NOTIFICACIONES DE CALIBRACIÓN');
        $this->newLine();

        // Mostrar estadísticas actuales
        $this->mostrarEstadisticas();

        // Verificar calibraciones próximas con información detallada
        $this->info('🔍 Verificando calibraciones próximas...');
        $resultado = $this->calibracionService->verificarCalibracionesProximas();
        
        $this->info("✅ Resultados:");
        $this->line("   - Equipos encontrados: {$resultado['equipos_encontrados']}");
        $this->line("   - Notificaciones creadas: {$resultado['notificaciones_creadas']}");
        $this->line("   - Coordinadores notificados: {$resultado['coordinadores_notificados']}");
        
        if (!empty($resultado['errores'])) {
            $this->warn("⚠️  Errores encontrados:");
            foreach ($resultado['errores'] as $error) {
                $this->line("   - {$error}");
            }
        }

        // Mostrar información detallada de depuración
        $this->mostrarInformacionDetallada();

        // Verificar calibraciones vencidas
        $this->newLine();
        $this->info('🔍 Verificando calibraciones vencidas...');
        $equiposVencidos = $this->calibracionService->verificarCalibracionesVencidas();
        
        if ($equiposVencidos['equipos_vencidos'] > 0) {
            $this->warn("⚠️  Equipos con calibración vencida: {$equiposVencidos['equipos_vencidos']}");
            foreach ($equiposVencidos['equipos'] as $equipo) {
                $this->line("   - {$equipo['equipamiento']} ({$equipo['marca_modelo']}) - Vencida hace {$equipo['dias_vencida']} días");
            }
        } else {
            $this->info("✅ No hay equipos con calibración vencida");
        }

        $this->newLine();
        $this->info('🎯 Prueba completada exitosamente');
        
        return 0;
    }

    private function mostrarEstadisticas(): void
    {
        $stats = $this->calibracionService->obtenerEstadisticasCalibracion();
        
        $this->info('📊 ESTADÍSTICAS ACTUALES:');
        $this->line("   Total de equipos activos: {$stats['total_equipos']}");
        $this->line("   Equipos con fecha de calibración: {$stats['equipos_con_calibracion']}");
        $this->line("   Próximos a calibración (24h): {$stats['proximos_24h']}");
        $this->line("   Próximos a calibración (7 días): {$stats['proximos_7dias']}");
        $this->line("   Calibraciones vencidas: {$stats['vencidos']}");
        $this->newLine();
    }

    private function mostrarInformacionDetallada(): void
    {
        $this->info('🔍 INFORMACIÓN DETALLADA DE DEPURACIÓN:');
        
        // Obtener equipos próximos a calibración
        $equiposProximos = InventarioLab::where('activo', true)
            ->whereNotNull('fecha_calibracion')
            ->where('fecha_calibracion', '<=', Carbon::now()->addDay())
            ->where('fecha_calibracion', '>', Carbon::now())
            ->get();

        $this->line("   Equipos encontrados en consulta: {$equiposProximos->count()}");
        
        foreach ($equiposProximos as $equipo) {
            $fechaCalibracion = Carbon::parse($equipo->fecha_calibracion);
            $horasRestantes = Carbon::now()->diffInHours($fechaCalibracion, false);
            $diasRestantes = Carbon::now()->diffInDays($fechaCalibracion, false);
            
            $this->line("   📋 Equipo: {$equipo->equipamiento}");
            $this->line("      - Marca/Modelo: {$equipo->marca_modelo}");
            $this->line("      - Serie: {$equipo->n_serie_lote}");
            $this->line("      - Fecha calibración: {$fechaCalibracion->format('d/m/Y H:i')}");
            $this->line("      - Horas restantes: {$horasRestantes}");
            $this->line("      - Días restantes: {$diasRestantes}");
            $this->line("      - Cumple condición (20-28h): " . ($horasRestantes >= 20 && $horasRestantes <= 28 ? 'SÍ' : 'NO'));
            
            // Verificar coordinadores
            $coordinadores = \App\Models\User::where('rol', 'coordinador_lab')
                ->orWhere('usu_nivel', '>=', 900)
                ->get();
            
            $this->line("      - Coordinadores encontrados: {$coordinadores->count()}");
            foreach ($coordinadores as $coordinador) {
                $this->line("        * {$coordinador->usu_descripcion} (Nivel: {$coordinador->usu_nivel}, Rol: {$coordinador->rol})");
            }
            
            // Verificar notificaciones existentes
            $mensaje = "⚠️ EQUIPO REQUIERE CALIBRACIÓN: {$equipo->equipamiento} ({$equipo->marca_modelo}) - Serie: {$equipo->n_serie_lote} - Fecha de calibración: {$fechaCalibracion->format('d/m/Y H:i')} - Faltan " . ($horasRestantes < 24 ? "{$horasRestantes} horas" : "{$diasRestantes} días");
            
            $notificacionesExistentes = \App\Models\SimpleNotification::where('mensaje', $mensaje)
                ->where('created_at', '>=', Carbon::now()->subHours(2))
                ->count();
            
            $this->line("      - Notificaciones similares (últimas 2h): {$notificacionesExistentes}");
        }
        
        $this->newLine();
    }
} 