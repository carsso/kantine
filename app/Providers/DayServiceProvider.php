<?php

namespace App\Providers;

use App\Services\DayService;
use Illuminate\Support\ServiceProvider;

class DayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(DayService::class, function ($app) {
            return new DayService();
        });
    }
} 