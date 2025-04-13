<?php

namespace App\Listeners;

use App\Events\JobFailed;
use App\Models\FailedJob;
use Illuminate\Support\Facades\Log;

class LogFailedJob
{
    public function handle(JobFailed $jobFailed)
    {
        try {
            $job = FailedJob::create([
                'uuid' => $jobFailed->uuid,
                'connection' => $jobFailed->connection,
                'queue' => $jobFailed->queue,
                'payload' => $jobFailed->payload,
                'exception' => $jobFailed->exception,
                'logs' => json_encode($jobFailed->logs),
                'failed_at' => $jobFailed->failed_at,
                'created_at' => $jobFailed->created_at,
                'updated_at' => now()
            ]);
            $job->created_at = $jobFailed->created_at;
            $job->failed_at = $jobFailed->failed_at;
            $job->save();
        } catch (\Exception $e) {
            Log::error('Failed to log failed job', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 