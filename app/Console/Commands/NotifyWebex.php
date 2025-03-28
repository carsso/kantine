<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\WebexNotificationService;
use Illuminate\Console\Command;

class NotifyWebex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kantine:notify-webex {tenant_slug?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send menu to Webex rooms';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(WebexNotificationService $webexService)
    {
        if($this->argument('tenant_slug')) {
            $tenants = Tenant::where('slug', $this->argument('tenant_slug'))->where('is_active', true)->get();
        } else {
            $tenants = Tenant::where('is_active', true)->get();
        }
        $date = date('Y-m-d');
        
        foreach($tenants as $tenant) {
            $this->info('--------------------------------');
            $this->info('Sending Webex notifications for menu of ' . $date . ' to all rooms for tenant ' . $tenant->name);
            
            $result = $webexService->sendMenuNotifications($tenant, $date);
            $this->info($result['message']);
        }
        
        $this->info('--------------------------------');
        return 0;
    }
}
