<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\DayService;
use Illuminate\Console\Command;
use App\Libraries\WebexApi;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessWebexMenuNotification;

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
    public function handle(DayService $dayService)
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
            $menu = $dayService->getDay($tenant, $date);

            if(!$tenant->webex_bearer_token) {
                $this->info('Webex bearer token not set for tenant ' . $tenant->name . ', skipping');
                continue;
            }
            if(!$menu) {
                $this->info('No menu for date '.$date.' for tenant '.$tenant->name.', skipping');
                continue;
            }
            if(!$menu['dishes'] || count($menu['dishes']) == 0) {
                $this->info('No dishes for date '.$date.' for tenant '.$tenant->name.', skipping');
                continue;
            }
            $api = new WebexApi($tenant);
            $this->info('Listing Webex rooms for tenant ' . $tenant->name);
            $rooms = $api->getRooms();
            foreach($rooms['items'] as $room) {
                $this->info('Adding Webex room notification task to room ' . $room['title'] .' ' . $room['id']);
                ProcessWebexMenuNotification::dispatch($tenant, $room, $menu, $date);
            }
        }
        $this->info('--------------------------------');
        return 0;
    }
}
