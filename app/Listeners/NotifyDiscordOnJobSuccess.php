<?php

namespace App\Listeners;

use App\Events\JobSuccessfullyProcessed;
use App\Services\DiscordNotificationService;

class NotifyDiscordOnJobSuccess
{
    public function __construct(private DiscordNotificationService $discordService) {}

    public function handle(JobSuccessfullyProcessed $event): void
    {
        $this->discordService->notifyJobSuccess(
            $event->uuid,
            json_decode($event->payload, true),
            $event->finished_at
        );
    }
}
