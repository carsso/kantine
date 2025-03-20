<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class TenantAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->route('tenant');
        if (!$tenant) {
            abort(404, 'Cantine non trouvée');
        }

        if(!auth()->check()) {
            abort(403, 'Accès refusé');
        }
        if(!auth()->user()->hasPermissionTo('tenant-admin-' . $tenant->slug)) {
            abort(403, 'Accès refusé');
        }

        return $next($request);
    }
}
