<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class JobSuccessfullyProcessed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $uuid,
        public string $connection,
        public string $queue,
        public string $payload,
        public array $logs = [],
        public ?string $created_at = null,
        public ?string $finished_at = null
    ) {}
} 