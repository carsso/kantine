<?php

namespace App\Providers;

use App\Events\JobSuccessfullyProcessed;
use App\Events\JobFailed;
use App\Listeners\LogSuccessfulJob;
use App\Listeners\LogFailedJob;
use App\Listeners\NotifySlackOnJobSuccess;
use App\Listeners\NotifySlackOnJobFailed;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        JobSuccessfullyProcessed::class => [
            LogSuccessfulJob::class,
            NotifySlackOnJobSuccess::class,
        ],
        JobFailed::class => [
            LogFailedJob::class,
            NotifySlackOnJobFailed::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
