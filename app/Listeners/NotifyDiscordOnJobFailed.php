<?php

namespace App\Listeners;

use App\Events\JobFailed;
use App\Services\DiscordNotificationService;

class NotifyDiscordOnJobFailed
{
    public function __construct(private DiscordNotificationService $discordService) {}

    public function handle(JobFailed $event): void
    {
        $this->discordService->notifyJobFailed(
            $event->uuid,
            json_decode($event->payload, true),
            $event->exception,
            $event->failed_at
        );
    }
}
