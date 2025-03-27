<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Tenant;
use App\Traits\JobLogging;
use App\Lib\ApiRestaurationClient;
use App\Jobs\UpdateMenusFromApiJob;
class CheckMenusFromApiJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, JobLogging;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;



    /**
     * Create a new job instance.
     */
    public function __construct(public Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handleLoggedJob(): void
    {
        if(!isset($this->tenant->meta['api_type']) || !$this->tenant->meta['api_type']) {
            throw new \Exception('API Type is not set');
        }

        if ($this->tenant->meta['api_type'] === 'api-restauration') {
            $client = new ApiRestaurationClient($this->tenant);
            $menus = $client->getMenus();
            $this->logJob('Menus retrieved successfully');
            $compareMenus = $client->compareMenus($menus);
            if(count($compareMenus) > 0) {
                $this->logJob('Changes to menus found', 'info', ['compareMenus' => $compareMenus]);
                UpdateMenusFromApiJob::dispatch($this->tenant)->delay(5);
                $this->logJob('UpdateMenusFromApiJob dispatched');
            } else {
                $this->logJob('No changes to menus', 'info');
                $this->doNotLog = true;
            }
        } else {
            throw new \Exception('Unsupported API type: ' . $this->tenant->meta['api_type']);
        }
    }

    public function uniqueId(): string
    {
        return $this->tenant->id;
    }
}
