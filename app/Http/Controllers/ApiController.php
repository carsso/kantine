<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Tenant;
use App\Services\DayService;

class ApiController extends Controller
{
    public function home()
    {
        $tenants = Tenant::where('is_active', true)->get();
        $routes = [];
        
        foreach ($tenants as $tenant) {
            $routes[$tenant->slug] = [
                'today' => route('api.today', ['tenant' => $tenant->slug]),
                'day' => route('api.day', ['tenant' => $tenant->slug, 'day' => date('Y-m-d')]),
            ];
        }

        return $routes;
    }

    public function today(DayService $dayService, $tenant)
    {
        return $this->day($dayService,  $tenant, date('Y-m-d'));
    }

    public function day(DayService $dayService, $tenant, $dateString)
    {
        return $dayService->getDay($tenant, $dateString);
    }
}
