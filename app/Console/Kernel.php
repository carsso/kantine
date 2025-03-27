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
        $schedule->command('kantine:notify-webex')
            ->weekdays()->at('9:30');

        $schedule->command('kantine:refresh-dashboard')
            ->dailyAt('00:30')
            ->dailyAt('15:30');

        $schedule->command('kantine:check-menus-from-api')
            ->everyTenMinutes();
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
