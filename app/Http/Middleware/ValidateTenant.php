<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class ValidateTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantSlug = $request->route('tenant');
        $tenant = Tenant::where('slug', $tenantSlug)->first();

        if (!$tenant) {
            abort(404, 'Cantine non trouvée');
        }

        if (!$tenant->is_active) {
            if(auth()->check() && auth()->user()->hasRole('Super Admin')) {
                session()->flash('flash_warning', 'La cantine ' . $tenant->name . ' est actuellement désactivée et n\'est pas visible publiquement. Toutefois, en tant qu\'administrateur, vous pouvez y accéder quand même.');
            } else {
                abort(404, 'Cantine non trouvée');
            }
        }

        $request->route()->setParameter('tenant', $tenant);

        return $next($request);
    }
}
