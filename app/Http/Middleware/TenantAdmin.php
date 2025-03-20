<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // If user is not authenticated, try Sanctum
        if (!Auth::check()) {
            app('auth:sanctum')->handle($request, function ($request) {
                return $request;
            });
        }

        $user = Auth::user();
        $tenant = $request->route('tenant');

        if (!$user || !$tenant) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        if(!$user->hasPermissionTo('tenant-admin-' . $tenant->slug)) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        return $next($request);
    }
}
