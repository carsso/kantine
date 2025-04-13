<?php

namespace App\Listeners;

use App\Events\JobFailed;
use App\Services\SlackNotificationService;

class NotifySlackOnJobFailed
{
    public function __construct(private SlackNotificationService $slackService)
    {
    }

    public function handle(JobFailed $event): void
    {
        $this->slackService->notifyJobFailed(
            $event->uuid,
            json_decode($event->payload, true),
            $event->exception,
            $event->failed_at
        );
    }
} 