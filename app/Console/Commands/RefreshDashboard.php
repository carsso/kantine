<?php

namespace App\Console\Commands;

use App\Events\DashboardRefreshEvent;
use App\Services\DayService;
use Illuminate\Console\Command;

class RefreshDashboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kantine:refresh-dashboard';

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
        DashboardRefreshEvent::dispatch();
        $this->info('Dashboard refreshed');
        return 0;
    }
}
