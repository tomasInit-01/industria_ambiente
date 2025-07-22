<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Verificar calibraciones del inventario de laboratorio diariamente a las 8:00 AM
        $schedule->command('inventario:verificar-calibracion')
                ->dailyAt('08:00')
                ->appendOutputTo(storage_path('logs/calibracion.log'))
                ->description('Verificar fechas de calibración del inventario de laboratorio');

        // Verificar muestras a punto de vencer
        $schedule->command('app:verificar-muestras-vencidas')
                ->dailyAt('08:00')
                ->appendOutputTo(storage_path('logs/muestras.log'))
                ->description('Verificar muestras a punto de vencer');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 