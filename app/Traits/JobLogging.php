<?php

namespace App\Traits;

use App\Events\JobFailed;
use App\Events\JobSuccessfullyProcessed;
use App\Models\Job;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Log;

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
            'date' => now(),
        ];
        Log::info($message, $data);
    }

    public function handle(): void
    {
        $container = Container::getInstance();
        $parameters = $this->getHandleLoggedJobParameters();

        try {
            if (empty($parameters)) {
                $this->handleLoggedJob();
            } else {
                $dependencies = array_map(function ($parameter) use ($container) {
                    $type = $parameter->getType();

                    return $container->make($type instanceof \ReflectionNamedType ? $type->getName() : (string) $type);
                }, $parameters);

                call_user_func_array([$this, 'handleLoggedJob'], $dependencies);
            }

            if ($this->doNotLog) {
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
        } catch (\Exception $e) {
            // Log l'erreur dans les logs du job
            $this->logJob($e->getMessage(), 'error', [
                'trace' => $e->getTraceAsString(),
            ]);

            // Dispatch l'événement d'échec
            JobFailed::dispatch(
                $this->job->uuid(),
                $this->job->getConnectionName(),
                $this->job->getQueue(),
                $this->job->getRawBody(),
                $this->jobLogs,
                $e->__toString(),
                $job->created_at ?? null,
                now()
            );

            throw $e;
        }
    }

    /**
     * Get the parameters for handleLoggedJob method
     */
    protected function getHandleLoggedJobParameters(): array
    {
        $reflection = new \ReflectionMethod($this, 'handleLoggedJob');

        return $reflection->getParameters();
    }
}
