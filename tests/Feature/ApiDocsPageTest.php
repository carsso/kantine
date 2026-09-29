<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\BuildsMenus;
use Tests\TestCase;

class ApiDocsPageTest extends TestCase
{
    use BuildsMenus;

    public function test_the_page_documents_the_api_and_the_mcp_server_of_the_canteen(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'Lille']);
        $slug = ['tenantSlug' => $tenant->slug];
        $mcpUrl = route('mcp', $slug);

        $response = $this->get(route('api-docs', $slug));

        $response->assertOk();
        $response->assertSee('Les menus de la cantine Lille');
        $response->assertSee(route('api.home', $slug));
        $response->assertSee("claude mcp add --transport http kantine-{$tenant->slug} {$mcpUrl}");
    }

    public function test_the_page_documents_every_public_api_route(): void
    {
        $tenant = Tenant::factory()->create();
        $prefix = 'api/{tenantSlug}';

        $paths = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => in_array('GET', $route->methods()))
            ->map(fn ($route) => $route->uri())
            ->filter(fn (string $uri) => str_starts_with($uri, $prefix.'/') && ! str_starts_with($uri, $prefix.'/admin'))
            ->map(fn (string $uri) => substr($uri, strlen($prefix)));
        $this->assertNotEmpty($paths);

        $response = $this->get(route('api-docs', ['tenantSlug' => $tenant->slug]));
        foreach ($paths as $path) {
            $response->assertSee("<code class=\"font-mono\">{$path}</code>", false);
        }
    }

    public function test_the_page_lists_every_tool_exposed_by_the_mcp_server(): void
    {
        $tenant = Tenant::factory()->create();

        $tools = $this->postJson(route('mcp', ['tenantSlug' => $tenant->slug]), [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/list',
        ])->json('result.tools');

        $response = $this->get(route('api-docs', ['tenantSlug' => $tenant->slug]));
        foreach ($tools as $tool) {
            $response->assertSee('>'.e($tool['name']).'</code> : '.e($tool['title']).'.', false);
        }
    }

    public function test_the_example_shows_the_real_response_of_the_api(): void
    {
        $this->travelTo('2026-03-04 12:00:00');
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-03-02', 'Frites');
        $this->createDish($child, '2026-03-06', 'Steak frites');

        $this->get(route('api-docs', ['tenantSlug' => $tenant->slug]))
            ->assertOk()
            ->assertSee('&quot;total&quot;: 1', false)
            ->assertSee('Steak frites');
    }

    public function test_the_navbar_links_to_the_page(): void
    {
        $tenant = Tenant::factory()->create();

        $this->get(route('menus', ['tenantSlug' => $tenant->slug]))
            ->assertOk()
            ->assertSee('API \\/ MCP', false)
            ->assertSee(str_replace('/', '\/', route('api-docs', ['tenantSlug' => $tenant->slug])), false);
    }

    public function test_inactive_canteens_have_no_page(): void
    {
        $tenant = Tenant::factory()->inactive()->create();

        $this->get(route('api-docs', ['tenantSlug' => $tenant->slug]))->assertNotFound();
    }
}
