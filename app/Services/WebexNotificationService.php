<?php

namespace App\Services;

use App\Models\Tenant;
use App\Libraries\WebexApi;
use App\Jobs\ProcessWebexMenuNotification;
use Illuminate\Support\Facades\Log;

class WebexNotificationService
{
    protected $dayService;
    protected $api;

    public function __construct(DayService $dayService)
    {
        $this->dayService = $dayService;
    }

    /**
     * Envoie les notifications Webex pour un menu à toutes les salles d'un tenant
     *
     * @param Tenant $tenant
     * @param string $date
     * @return array
     */
    public function sendMenuNotifications(Tenant $tenant, string $date, bool $notifyUpdate = false, string $initiator = null): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'notifications_sent' => 0
        ];

        if (!$tenant->webex_bearer_token) {
            $result['message'] = 'Webex bearer token not set for tenant ' . $tenant->name;
            return $result;
        }

        $menu = $this->dayService->getDay($tenant, $date);

        if (!$menu) {
            $result['message'] = 'No menu for date ' . $date . ' for tenant ' . $tenant->name;
            return $result;
        }

        if (!$menu['dishes'] || count($menu['dishes']) == 0) {
            $result['message'] = 'No dishes for date ' . $date . ' for tenant ' . $tenant->name;
            return $result;
        }

        $api = new WebexApi($tenant);
        $rooms = $api->getRooms();

        foreach ($rooms['items'] as $room) {
            ProcessWebexMenuNotification::dispatch($tenant, $room, $menu, $date, $notifyUpdate, $initiator);
            $result['notifications_sent']++;
        }

        $result['success'] = true;
        $result['message'] = 'Successfully queued ' . $result['notifications_sent'] . ' notifications for tenant ' . $tenant->name;

        return $result;
    }
} 