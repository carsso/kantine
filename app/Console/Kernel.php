<?php

namespace App\Console;

use App\Models\Tenant;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $tenants = Tenant::where('is_active', true)->get();
        foreach($tenants as $tenant) {
            if($tenant->webex_bearer_token) {
                $schedule->command('kantine:notify-webex', ['tenant_slug' => $tenant->slug])
                    ->weekdays()->at('10:30');
            }

            $schedule->command('kantine:refresh-dashboard', ['tenant_slug' => $tenant->slug])
                ->dailyAt('00:30')
                ->dailyAt('15:30');
        }
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
