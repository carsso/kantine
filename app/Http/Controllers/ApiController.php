<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\DayService;
use App\Services\MenuSearchService;
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
                'menus' => route('api.menus', ['tenantSlug' => $tenant->slug]),
                'dishes' => route('api.dishes', ['tenantSlug' => $tenant->slug]),
                'events' => route('api.events', ['tenantSlug' => $tenant->slug]),
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

    public function menus(Request $request, MenuSearchService $menuSearchService)
    {
        $validated = $request->validate(MenuSearchService::dateRangeRules());

        return $menuSearchService->menus($request->route('tenant'), $validated['start_date'] ?? null, $validated['end_date'] ?? null);
    }

    public function dishes(Request $request, MenuSearchService $menuSearchService)
    {
        if (is_string($request->query('tags'))) {
            $request->merge(['tags' => explode(',', $request->query('tags'))]);
        }

        return $menuSearchService->searchDishes($request->route('tenant'), $request->validate(MenuSearchService::searchRules()));
    }

    public function events(Request $request, MenuSearchService $menuSearchService)
    {
        $validated = $request->validate(MenuSearchService::dateRangeRules());

        return $menuSearchService->events($request->route('tenant'), $validated['start_date'] ?? null, $validated['end_date'] ?? null);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
