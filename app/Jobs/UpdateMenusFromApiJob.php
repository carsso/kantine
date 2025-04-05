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
use App\Services\WebexNotificationService;

class UpdateMenusFromApiJob implements ShouldQueue, ShouldBeUnique
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

    public function handleLoggedJob(WebexNotificationService $webexService): void
    {
        if(!isset($this->tenant->meta['api_type']) || !$this->tenant->meta['api_type']) {
            throw new \Exception('API Type is not set');
        }

        if ($this->tenant->meta['api_type'] === 'api-restauration') {
            $logCallback = function($message, $level = 'info', $data = []) {
                $this->logJob($message, $level, $data);
            };
            
            $client = new ApiRestaurationClient($this->tenant, $logCallback);
            $menus = $client->getMenus();
            if($menus) {
                $this->logJob('Menus retrieved successfully', 'info', ['menus' => $menus]);
                $today = now()->format('Y-m-d');
                $menusDiff = $client->compareMenus($menus);
                if(count($menusDiff) > 0) {
                    $this->logJob('Changes to menus found', 'info', ['menusDiff' => $menusDiff]);
                    $client->updateMenus($menus);
                    $this->logJob('Menus updated successfully', 'info');

                    $menusDiff2 = $client->compareMenus($menus);
                    if(count($menusDiff2) == 0) {
                        $this->logJob('After updating menus, no differences found, fine', 'info');
                        if(isset($menusDiff[$today])) {
                            $currentTime = now();
                            if ($currentTime->hour >= 9 && $currentTime->hour <= 13) {
                                if ($currentTime->hour === 9 && $currentTime->minute < 30) {
                                    return;
                                }
                                if ($currentTime->hour === 13 && $currentTime->minute > 30) {
                                    return;
                                }
                                
                                $result = $webexService->sendMenuNotifications($this->tenant, $today, true, 'API Restauration (API)');
                                if ($result['success']) {
                                    $this->logJob('Webex notifications requested successfully: ' . $result['message'], 'info');
                                } else {
                                    $this->logJob('Failed to request Webex notifications: ' . $result['message'], 'error');
                                }
                            }
                        }
                    } else {
                        $this->logJob('After updating menus, we still have differences', 'error', ['menusDiff2' => $menusDiff2]);
                        throw new \Exception('After updating menus, we still have differences');
                    }
                } else {
                    $this->logJob('No changes to menus', 'info');
                    $this->doNotLog = true;
                }
            } else {
                $this->logJob('No menus found', 'info');
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
