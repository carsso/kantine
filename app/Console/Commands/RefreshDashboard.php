<?php

namespace App\Console\Commands;

use App\Events\DashboardRefreshEvent;
use App\Events\MenuUpdatedEvent;
use App\Models\Menu;
use Illuminate\Console\Command;

class RefreshDashboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kantine:refresh-dashboard {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh dashboard page';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if($date = $this->argument('date')) {
            $menu = Menu::where('date', $date)->first();
            if(!$menu) {
                $this->error('Menu not found for date: ' . $date);
                return 1;
            }
            MenuUpdatedEvent::dispatch($menu);
            $this->info('Menu updated for date: ' . $date);
        } else {
            DashboardRefreshEvent::dispatch();
            $this->info('Dashboard refreshed');
        }
        return 0;
    }
}
