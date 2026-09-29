<?php

namespace App\Http\Controllers;

use App\Mcp\Servers\KantineServer;
use App\Models\Dish;
use App\Providers\RouteServiceProvider;
use App\Services\MenuSearchService;
use Illuminate\Http\Request;
use Laravel\Mcp\Server\Tool;
use Symfony\Component\HttpFoundation\Response;

class ApiDocsController extends Controller
{
    public function show(Request $request, MenuSearchService $menuSearchService): Response
    {
        $tenant = $request->route('tenant');
        $slug = ['tenantSlug' => $tenant->slug];
        $today = today()->format('Y-m-d');

        $endpoints = [
            [
                'path' => '/today',
                'description' => 'Le menu du jour, tel qu\'affiché sur le site, avec les prochains jours frites, burgers et événements.',
                'example' => route('api.today', $slug),
            ],
            [
                'path' => '/day/{date}',
                'description' => 'Le même contenu pour une date donnée (AAAA-MM-JJ).',
                'example' => route('api.day', [...$slug, 'date' => $today]),
            ],
            [
                'path' => '/menus',
                'description' => 'Les menus d\'une période, dans un format compact. Les jours sans menu ni annonce sont omis.',
                'params' => [
                    'start_date' => 'premier jour, aujourd\'hui par défaut',
                    'end_date' => 'dernier jour inclus, start_date par défaut ('.MenuSearchService::MAX_MENU_DAYS.' jours maximum)',
                ],
                'example' => route('api.menus', [
                    ...$slug,
                    'start_date' => today()->startOfWeek()->format('Y-m-d'),
                    'end_date' => today()->startOfWeek()->addDays(4)->format('Y-m-d'),
                ]),
            ],
            [
                'path' => '/dishes',
                'description' => 'Recherche de plats, passés et à venir, avec les dates où ils sont servis.',
                'params' => [
                    'query' => 'mots que le nom du plat doit tous contenir',
                    'tags' => 'tags séparés par des virgules (voir ci-dessous)',
                    'start_date / end_date' => 'bornes de dates',
                    'order' => 'asc (par défaut) ou desc',
                    'limit' => MenuSearchService::DEFAULT_SEARCH_RESULTS.' par défaut, '.MenuSearchService::MAX_SEARCH_RESULTS.' maximum',
                ],
                'example' => route('api.dishes', [...$slug, 'query' => 'frites', 'start_date' => $today]),
            ],
            [
                'path' => '/events',
                'description' => 'Les événements et annonces, à venir par défaut.',
                'params' => [
                    'start_date' => 'premier jour, aujourd\'hui par défaut',
                    'end_date' => 'dernier jour inclus, sans limite par défaut',
                ],
                'example' => route('api.events', $slug),
            ],
        ];

        $exampleQuery = ['query' => 'frites', 'start_date' => $today, 'limit' => 1];

        return response()->view('api-docs', [
            'tenant' => $tenant,
            'apiUrl' => route('api.home', $slug),
            'mcpUrl' => route('mcp', $slug),
            'endpoints' => $endpoints,
            'tags' => Dish::getTagsDefinitions(),
            'requestsPerMinute' => RouteServiceProvider::API_REQUESTS_PER_MINUTE,
            'mcpRequestsPerMinute' => RouteServiceProvider::MCP_REQUESTS_PER_MINUTE,
            'mcpTools' => collect(KantineServer::TOOLS)
                ->map(fn (string $class): Tool => app($class))
                ->map(fn (Tool $tool): array => ['name' => $tool->name(), 'title' => $tool->title()])
                ->all(),
            'exampleUrl' => route('api.dishes', [...$slug, ...$exampleQuery]),
            'exampleResponse' => $menuSearchService->searchDishes($tenant, $exampleQuery),
        ]);
    }

    /**
     * Browsers opening the MCP endpoint land on the MCP section of this page.
     */
    public function mcp(Request $request): Response
    {
        // MCP clients probing for an SSE stream still get the 405 the MCP spec expects.
        if (str_contains($request->header('Accept', ''), 'text/event-stream')) {
            return response('', 405)->header('Allow', 'POST');
        }

        return redirect()->to(route('api-docs', ['tenantSlug' => $request->route('tenant')->slug]).'#mcp');
    }
}
