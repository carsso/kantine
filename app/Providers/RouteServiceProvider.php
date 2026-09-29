<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/account';

    public const API_REQUESTS_PER_MINUTE = 60;

    /**
     * Higher than the API: claude.ai connectors share a few egress IPs and one question costs several calls.
     */
    public const MCP_REQUESTS_PER_MINUTE = 300;

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(self::API_REQUESTS_PER_MINUTE)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('mcp', function (Request $request) {
            return Limit::perMinute(self::MCP_REQUESTS_PER_MINUTE)->by($request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
