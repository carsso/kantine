<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\DayService;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function home()
    {
        $tenants = Tenant::where('is_active', true)->get();
        $routes = [];
        
        foreach ($tenants as $tenant) {
            $routes[$tenant->slug] = [
                'today' => route('api.today', ['tenantSlug' => $tenant->slug]),
                'day' => route('api.day', ['tenantSlug' => $tenant->slug, 'date' => date('Y-m-d')]),
            ];
        }

        return $routes;
    }

    public function today(Request $request, DayService $dayService)
    {
        return $this->day($request, $dayService);
    }

    public function day(Request $request, DayService $dayService)
    {
        $tenant = $request->route('tenant');
        $dateString = $request->route('date');
        return $dayService->getDay($tenant, $dateString);
    }
    
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
