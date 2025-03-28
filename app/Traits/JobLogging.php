<?php

namespace App\Traits;

use App\Models\Job;
use Illuminate\Support\Facades\Log;
use App\Events\JobSuccessfullyProcessed;
use Illuminate\Container\Container;

trait JobLogging
{
    public $jobLogs = [];

    public $doNotLog = false;

    public function logJob($message, $level = 'info', $data = [])
    {
        $this->jobLogs[] = [
            'message' => $message,
            'level' => $level,
            'data' => $data,
            'date' => now()
        ];
        Log::info($message, $data);
    }

    public function handle(): void
    {
        $container = Container::getInstance();
        $parameters = $this->getHandleLoggedJobParameters();
        
        if (empty($parameters)) {
            $this->handleLoggedJob();
        } else {
            $dependencies = array_map(function ($parameter) use ($container) {
                return $container->make($parameter->getClass()->getName());
            }, $parameters);
            
            call_user_func_array([$this, 'handleLoggedJob'], $dependencies);
        }

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

    /**
     * Get the parameters for handleLoggedJob method
     *
     * @return array
     */
    protected function getHandleLoggedJobParameters(): array
    {
        $reflection = new \ReflectionMethod($this, 'handleLoggedJob');
        return $reflection->getParameters();
    }
} 