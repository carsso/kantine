<?php

namespace App\Listeners;

use App\Events\JobSuccessfullyProcessed;
use App\Models\SuccessfulJob;
use Illuminate\Support\Facades\Log;

class LogSuccessfulJob
{
    public function handle(JobSuccessfullyProcessed $jobSuccessfullyProcessed)
    {
        try {
            $job = SuccessfulJob::create([
                'uuid' => $jobSuccessfullyProcessed->uuid,
                'connection' => $jobSuccessfullyProcessed->connection,
                'queue' => $jobSuccessfullyProcessed->queue,
                'payload' => $jobSuccessfullyProcessed->payload,
                'result' => json_encode($jobSuccessfullyProcessed->logs),
                'finished_at' => $jobSuccessfullyProcessed->finished_at,
                'created_at' => $jobSuccessfullyProcessed->created_at,
                'updated_at' => now()
            ]);
            $job->created_at = $jobSuccessfullyProcessed->created_at;
            $job->finished_at = $jobSuccessfullyProcessed->finished_at;
            $job->save();
        } catch (\Exception $e) {
            Log::error('Failed to log successful job', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 