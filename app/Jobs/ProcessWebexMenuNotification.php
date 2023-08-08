<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Menu;
use App\Libraries\WebexApi;
use Illuminate\Support\Facades\Log;

class ProcessWebexMenuNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $room, public ?Menu $menu)
    {
        $this->room = $room;
        $this->menu = $menu;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if(!config('services.webex.bearer_token')) {
            Log::info('Webex bearer token not set, aborting');
            return;
        }
        $api = new WebexApi;
        $html = view('webex.menu', ['menu' => $this->menu])->render();
        $html = preg_replace('/\s{2,}/', ' ', $html);
        Log::info('Posting message to Webex room "' . $this->room['title'] .'" ' . $this->room['id']);
        Log::info($html);
        $api->postMessage($this->room['id'], $html);
    }
}
