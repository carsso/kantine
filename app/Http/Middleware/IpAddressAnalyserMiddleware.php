<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\IpUtils;
use Illuminate\Support\Facades\View;

class IpAddressAnalyserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isIpAllowed = $this->isIpAllowed($request->ip());
        $request->merge(['isIpAllowed' => $isIpAllowed]);
        View::share('isIpAllowed', $isIpAllowed);
        return $next($request);
    }

    public function isIpAllowed($requestIp): bool
    {
        $ipList = config('ip.allow_list');
        if(empty($ipList)) {
            return true;
        }
        $ipListResolved = [];
        foreach ($ipList as $ip) {
            if(strpos($ip, '/') === false) {
                if(filter_var($ip, FILTER_VALIDATE_IP) === false) {
                    $ip = gethostbyname($ip);
                }
            }
            $ipListResolved[] = $ip;
        }
        if(IpUtils::checkIp($requestIp, $ipListResolved)) {
            return true;
        }
        return false;
    }
}
