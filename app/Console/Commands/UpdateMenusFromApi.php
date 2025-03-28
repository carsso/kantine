<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\DayService;
use Illuminate\Console\Command;
use App\Jobs\UpdateMenusFromApiJob;    

class UpdateMenusFromApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kantine:update-menus-from-api {tenant_slug?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update menus from API';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(DayService $dayService)
    {
        if($this->argument('tenant_slug')) {
            $tenants = Tenant::where('slug', $this->argument('tenant_slug'))->get();
        } else {
            $tenants = Tenant::where('is_active', true)->get();
        }
        foreach($tenants as $tenant) {
            if(isset($tenant->meta['api_type']) && $tenant->meta['api_type']) {
                if($tenant->meta['api_type'] === 'api-restauration') {
                    $this->info('Checking menus from API for tenant '.$tenant->slug);
                    UpdateMenusFromApiJob::dispatch($tenant);
                    $this->info('Job dispatched for tenant '.$tenant->slug);
                } else {
                    $this->info('Tenant '.$tenant->slug.' has an unknown API Type, skipping');
                }
            } else {
                $this->info('Tenant '.$tenant->slug.' has no API URL or API Type, skipping');
            }
        }
        return 0;
    }
}
