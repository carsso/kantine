<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Libraries\WebexApi;
use Illuminate\Support\Facades\Log;
use App\Jobs\ProcessWebexMenuNotification;
use App\Models\Menu;

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
    public function handle()
    {
        $date = date('Y-m-d');
        Log::info('Sending Webex notifications for menu of ' . $date . ' to all rooms');
        $menu = Menu::where('date', $date)->where('mains', '!=', '[]')->where('sides', '!=', '[]')->first();

        if(!config('services.webex.bearer_token')) {
            Log::info('Webex bearer token not set, aborting');
            return 0;
        }
        if(!$menu) {
            Log::info('No menu for date '.$date.', aborting');
            return 0;
        }
        $api = new WebexApi;
        Log::info('Listing Webex rooms');
        $rooms = $api->getRooms();
        foreach($rooms['items'] as $room) {
            Log::info('Adding Webex room notification task to room ' . $room['title'] .' ' . $room['id']);
            ProcessWebexMenuNotification::dispatch($room, $menu, $date);
        }
        return 0;
    }
}
