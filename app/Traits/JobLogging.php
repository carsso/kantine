<?php

namespace App\Traits;

use App\Models\Job;
use Illuminate\Support\Facades\Log;
use App\Events\JobSuccessfullyProcessed;

trait JobLogging
{
    public $jobLogs = [];

    public $doNotLog = false;

    public function logJob($message, $level = 'info', $data = [])
    {
        $this->jobLogs[] = [
            'message' => $message,
            'level' => $level,
            'data' => $data
        ];
        Log::info($message, $data);
    }

    public function handle(): void
    {
        $this->handleLoggedJob();
        if($this->doNotLog) {
            return;
        }

        $job = Job::findOrFail($this->job->getJobId());

        // Dispatch l'événement de succès
        JobSuccessfullyProcessed::dispatch(
            $this->job->uuid(),
            $this->job->getConnectionName(),
            $this->job->getQueue(),
            $this->job->getRawBody(),
            $this->jobLogs,
            $job->created_at,
            now()
        );
    }
} 