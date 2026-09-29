<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetMenusTool;
use App\Mcp\Tools\ListEventsTool;
use App\Mcp\Tools\SearchDishesTool;
use App\Models\Tenant;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Version;

/**
 * One server per canteen: the canteen comes from the endpoint URL (/{tenantSlug}/mcp).
 */
#[Version('1.0.0')]
class KantineServer extends Server
{
    public const TOOLS = [
        GetMenusTool::class,
        SearchDishesTool::class,
        ListEventsTool::class,
    ];

    protected array $tools = self::TOOLS;

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];

    protected function boot(): void
    {
        /** @var Tenant $canteen */
        $canteen = request()->route('tenant');

        $this->name = 'Kantine '.$canteen->name;
        $this->instructions = "Read-only access to the lunch menus of the {$canteen->name} canteen. "
            .'Use get-menus for the menu of given days (today by default), search-dishes to find when a dish is served, '
            .'and list-events for special events. Menus are in French; dates are YYYY-MM-DD and responses include the weekday.';
    }
}
