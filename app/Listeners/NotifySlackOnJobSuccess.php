<?php

namespace App\Listeners;

use App\Events\JobSuccessfullyProcessed;
use App\Services\SlackNotificationService;

class NotifySlackOnJobSuccess
{
    public function __construct(private SlackNotificationService $slackService)
    {
    }

    public function handle(JobSuccessfullyProcessed $event): void
    {
        $this->slackService->notifyJobSuccess(
            $event->uuid,
            json_decode($event->payload, true),
            $event->finished_at
        );
    }
}