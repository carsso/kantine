<?php

namespace App\Console\Commands;

use App\Events\DashboardRefreshEvent;
use App\Events\MenuUpdatedEvent;
use App\Models\Menu;
use App\Models\Tenant;
use App\Services\DayService;
use Illuminate\Console\Command;

class RefreshDashboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kantine:refresh-dashboard {tenant_slug} {date?}';

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
    public function handle(DayService $dayService)
    {
        if($date = $this->argument('date')) {
            $tenant = Tenant::where('slug', $this->argument('tenant_slug'))->first();
            if(!$tenant) {
                $this->error('Tenant not found');
                return 1;
            }
            $menu = $dayService->getDay($tenant, $date);
            if(!$menu) {
                $this->error('Menu not found for date: ' . $date);
                return 1;
            }
            MenuUpdatedEvent::dispatch($menu);
            $this->info('Menu updated for date: ' . $date . ' for tenant ' . $tenant->name);
        } else {
            DashboardRefreshEvent::dispatch();
            $this->info('Dashboard refreshed');
        }
        return 0;
    }
}
