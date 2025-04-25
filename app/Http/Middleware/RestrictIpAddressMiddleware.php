<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictIpAddressMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if($request->isIpAllowed) {
            return $next($request);
        }
        abort(403, 'Cette page est restreinte pour des raisons de sécurité. Vous devez être sur le réseau interne, wifi ou VPN pour y accéder.');
    }     
}
