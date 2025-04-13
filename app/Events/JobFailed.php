<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $uuid,
        public string $connection,
        public string $queue,
        public string $payload,
        public array $logs = [],
        public string $exception,
        public ?string $created_at = null,
        public ?string $failed_at = null
    ) {}
} 