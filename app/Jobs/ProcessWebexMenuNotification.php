<?php

namespace App\Jobs;

use App\Models\DishCategory;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Libraries\WebexApi;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant;

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
    public function __construct(public Tenant $tenant, public array $room, public array $menu, public ?string $date, public ?bool $notifyUpdate = false)
    {
        $this->tenant = $tenant;
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
        if (!$this->tenant || !$this->tenant->webex_bearer_token) {
            Log::info('Webex configuration not found for tenant, aborting');
            return;
        }

        if ($this->room['isReadOnly']) {
            Log::info('Skipping read-only Webex room "' . $this->room['title'] .'" ' . $this->room['id']);
            return;
        }
        if(!$this->menu) {
            Log::info('No menu for date '.$this->date.', skipping');
            return;
        }
        $api = new WebexApi($this->tenant);
        $date = Carbon::parse($this->date);
        $categories = DishCategory::where('tenant_id', $this->tenant->id)
            ->whereNull('parent_id')
            ->with(['children' => function($query) {
                $query->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('type');
        $html = view('webex.menu', ['tenant' => $this->tenant, 'menu' => $this->menu, 'date' => $date, 'categories' => $categories])->render();
        Log::info('Working on Webex room "' . $this->room['title'] .'" ' . $this->room['id']);
        $messages = $api->getMessages($this->room['id']);
        sleep(5);
        Log::info($messages);
        foreach ($messages['items'] as $message) {
            if($message['personEmail'] !== $this->tenant->webex_bot_name) {
                continue;
            }
            if(str_contains($message['text'], route('menus', ['tenant' => $this->tenant->slug, 'date' => $this->date]))) {
                Log::info('Updating message in Webex room "' . $this->room['title'] .'" ' . $this->room['id']);
                Log::info($html);
                try {
                    $api->updateMessage($message['id'], $this->room['id'], $html);
                } catch (\Exception $e) {
                    if (str_contains($e->getMessage(), 'Max allowed number of edits per activity reached')) {
                        Log::error($e);
                        Log::info('Max allowed number of edits per activity reached. Posting a new message instead.');
                        $api->postMessage($this->room['id'], $html);
                        sleep(5);
                        return;
                    } else {
                        throw $e;
                    }
                }
                sleep(5);
                if(!$this->notifyUpdate) {
                    return;
                }
                $html = 'Menu mis à jour à ' . date('H\hi') . '<@personEmail:'.$this->tenant->webex_bot_name.'| >';
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
