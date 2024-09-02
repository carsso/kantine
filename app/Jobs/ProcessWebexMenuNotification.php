<?php

namespace App\Jobs;

use Carbon\Carbon;
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
    public function __construct(public array $room, public ?Menu $menu, public ?string $date, public ?bool $notifyUpdate = false)
    {
        $this->room = $room;
        $this->menu = $menu;
        $this->date = $date;
        $this->notifyUpdate = $notifyUpdate;
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
        if ($this->room['isReadOnly']) {
            Log::info('Skipping read-only Webex room "' . $this->room['title'] .'" ' . $this->room['id']);
            return;
        }
        $api = new WebexApi;
        $date = Carbon::parse($this->date);
        $html = view('webex.menu', ['menu' => $this->menu, 'date' => $date])->render();
        Log::info('Working on Webex room "' . $this->room['title'] .'" ' . $this->room['id']);
        $messages = $api->getMessages($this->room['id']);
        sleep(5);
        Log::info($messages);
        foreach ($messages['items'] as $message) {
            if($message['personEmail'] !== config('services.webex.bot_name')) {
                continue;
            }
            if(str_contains($message['text'], route('menu', ['date' => $this->date]))) {
                Log::info('Updating message in Webex room "' . $this->room['title'] .'" ' . $this->room['id']);
                Log::info($html);
                $api->upddateMessage($message['id'], $this->room['id'], $html);
                sleep(5);
                if(!$this->notifyUpdate) {
                    return;
                }
                $html = 'Menu mis à jour à ' . date('H\hi');
                Log::info('Posting reply message in Webex room "' . $this->room['title'] .'" ' . $this->room['id']);
                Log::info($html);
                $api->postMessage($this->room['id'], $html, $message['id']);
                sleep(5);
                return;
            }
        }

        Log::info('Posting message to Webex room "' . $this->room['title'] .'" ' . $this->room['id']);
        Log::info($html);
        $api->postMessage($this->room['id'], $html);
        sleep(5);
    }
}
