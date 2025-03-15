<?php

namespace App\Console\Commands;

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
    protected $signature = 'kantine:notify-webex';

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
        $date = date('Y-m-d');
        $this->info('Sending Webex notifications for menu of ' . $date . ' to all rooms');
        $menu = $dayService->getDay($date);

        if(!config('services.webex.bearer_token')) {
            $this->info('Webex bearer token not set, aborting');
            return 0;
        }
        if(!$menu) {
            $this->info('No menu for date '.$date.', aborting');
            return 0;
        }
        if(!$menu['dishes'] || count($menu['dishes']) == 0) {
            $this->info('No dishes for date '.$date.', aborting');
            return 0;
        }
        $api = new WebexApi;
        $this->info('Listing Webex rooms');
        $rooms = $api->getRooms();
        foreach($rooms['items'] as $room) {
            $this->info('Adding Webex room notification task to room ' . $room['title'] .' ' . $room['id']);
            ProcessWebexMenuNotification::dispatch($room, $menu, $date);
        }
        return 0;
    }
}
