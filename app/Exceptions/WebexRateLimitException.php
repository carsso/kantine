<?php

namespace App\Exceptions;

use Exception;

class WebexRateLimitException extends Exception
{
    protected $retryAfter;

    public function __construct(string $message = 'Rate limit exceeded', int $retryAfter = null, int $code = 429, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }

    public function getRetryAfterSeconds(): int
    {
        return $this->retryAfter ?? 60; // Default to 60 seconds if not specified
    }
}
