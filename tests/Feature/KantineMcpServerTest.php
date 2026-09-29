<?php

namespace Tests\Feature;

use App\Models\Information;
use App\Models\Tenant;
use App\Providers\RouteServiceProvider;
use Illuminate\Testing\TestResponse;
use Tests\Concerns\BuildsMenus;
use Tests\TestCase;

class KantineMcpServerTest extends TestCase
{
    use BuildsMenus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-03-04 12:00:00');
    }

    /**
     * Send a JSON-RPC request to the MCP server of the canteen.
     *
     * @param  array<string, mixed>  $params
     */
    protected function mcp(Tenant $tenant, string $method, array $params = []): TestResponse
    {
        return $this->postJson(route('mcp', ['tenantSlug' => $tenant->slug]), [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => $method,
            'params' => (object) $params,
        ]);
    }

    /**
     * Call a tool and return its structured content, failing if the tool errored.
     *
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    protected function callTool(Tenant $tenant, string $tool, array $arguments = []): array
    {
        $response = $this->mcp($tenant, 'tools/call', ['name' => $tool, 'arguments' => (object) $arguments])->assertOk();
        $this->assertFalse($response->json('result.isError'), (string) $response->json('result.content.0.text'));

        return $response->json('result.structuredContent');
    }

    /**
     * Call a tool that is expected to fail and return its error message.
     *
     * @param  array<string, mixed>  $arguments
     */
    protected function callToolError(Tenant $tenant, string $tool, array $arguments = []): string
    {
        $response = $this->mcp($tenant, 'tools/call', ['name' => $tool, 'arguments' => (object) $arguments])->assertOk();
        $this->assertTrue($response->json('result.isError'));

        return $response->json('result.content.0.text');
    }

    public function test_each_canteen_has_its_own_server_exposing_read_only_tools(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'Lille']);

        $initialize = $this->mcp($tenant, 'initialize', [
            'protocolVersion' => '2025-06-18',
            'capabilities' => (object) [],
            'clientInfo' => ['name' => 'test', 'version' => '1.0'],
        ])->assertOk();
        $this->assertSame('Kantine Lille', $initialize->json('result.serverInfo.name'));
        $this->assertStringContainsString('Lille canteen', $initialize->json('result.instructions'));

        $tools = collect($this->mcp($tenant, 'tools/list')->assertOk()->json('result.tools'));
        $this->assertEqualsCanonicalizing(['get-menus', 'search-dishes', 'list-events'], $tools->pluck('name')->all());
        $this->assertTrue($tools->every(fn (array $tool) => $tool['annotations']['readOnlyHint'] === true));
    }

    public function test_unknown_and_inactive_canteens_have_no_server(): void
    {
        $inactive = Tenant::factory()->inactive()->create();

        $this->mcp($inactive, 'tools/list')->assertNotFound();
        $this->postJson('/nowhere/mcp', ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list'])->assertNotFound();
    }

    public function test_get_menus_defaults_to_today(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-03-04', 'Poulet rôti', ['halal']);
        $this->createDish($child, '2026-03-05', 'Lasagnes');

        $content = $this->callTool($tenant, 'get-menus');

        $this->assertSame($tenant->name, $content['canteen']);
        $this->assertSame('2026-03-04', $content['today']);
        $this->assertCount(1, $content['days']);
        $this->assertSame('2026-03-04', $content['days'][0]['date']);
        $this->assertSame([[
            'name' => 'Poulet rôti',
            'tags' => ['halal'],
            'type' => 'mains',
            'category' => 'Pole Chaud',
            'sub_category' => 'Plats',
        ]], $content['days'][0]['dishes']);
    }

    public function test_get_menus_only_returns_the_menus_of_its_canteen(): void
    {
        $tenant = Tenant::factory()->create();
        $other = Tenant::factory()->create();
        [, $otherChild] = $this->createCategoryTree($other);
        $this->createDish($otherChild, '2026-03-04', 'Lasagnes');

        $this->assertSame([], $this->callTool($tenant, 'get-menus')['days']);
    }

    public function test_get_menus_returns_a_range_skipping_empty_days_and_sorting_dishes_by_category(): void
    {
        $tenant = Tenant::factory()->create();
        [, $desserts] = $this->createCategoryTree($tenant, 'desserts', 'Desserts', 'Fruits', 2);
        [, $mains] = $this->createCategoryTree($tenant, 'mains', 'Pole Chaud', 'Plats', 1);
        $this->createDish($desserts, '2026-03-02', 'Pomme');
        $this->createDish($mains, '2026-03-02', 'Couscous');
        $this->createDish($mains, '2026-03-06', 'Poisson pané');
        Information::factory()->event('Mardi gras')->create(['tenant_id' => $tenant->id, 'date' => '2026-03-03']);

        $days = $this->callTool($tenant, 'get-menus', ['start_date' => '2026-03-02', 'end_date' => '2026-03-06'])['days'];

        $this->assertSame(['2026-03-02', '2026-03-03', '2026-03-06'], array_column($days, 'date'));
        $this->assertSame('lundi', $days[0]['weekday']);
        $this->assertSame(['Couscous', 'Pomme'], array_column($days[0]['dishes'], 'name'));
        $this->assertSame('Mardi gras', $days[1]['event_name']);
        $this->assertSame([], $days[1]['dishes']);
    }

    public function test_get_menus_rejects_an_invalid_range(): void
    {
        $tenant = Tenant::factory()->create();

        $this->assertStringContainsString(
            'The date range cannot exceed 31 days.',
            $this->callToolError($tenant, 'get-menus', ['start_date' => '2026-03-01', 'end_date' => '2026-04-15'])
        );
        $this->assertStringContainsString(
            'The end_date must be on or after the start_date.',
            $this->callToolError($tenant, 'get-menus', ['start_date' => '2026-03-05', 'end_date' => '2026-03-01'])
        );
        $this->callToolError($tenant, 'get-menus', ['start_date' => 'tomorrow']);
    }

    public function test_search_dishes_matches_every_word_of_the_query_in_date_order(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-03-10', 'Poulet au curry');
        $this->createDish($child, '2026-02-10', 'Curry de poulet', ['halal']);
        $this->createDish($child, '2026-03-11', 'Poulet rôti');

        $other = Tenant::factory()->create();
        [, $otherChild] = $this->createCategoryTree($other);
        $this->createDish($otherChild, '2026-03-10', 'Poulet curry');

        $content = $this->callTool($tenant, 'search-dishes', ['query' => 'curry POULET']);

        $this->assertSame(2, $content['total']);
        $this->assertSame(['Curry de poulet', 'Poulet au curry'], array_column($content['results'], 'name'));
        $this->assertSame('2026-02-10', $content['results'][0]['date']);
        $this->assertSame('mardi', $content['results'][0]['weekday']);
        $this->assertSame(['halal'], $content['results'][0]['tags']);
    }

    public function test_search_dishes_filters_by_tags_dates_order_and_limit(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-03-02', 'Frites', ['vegetarian']);
        $this->createDish($child, '2026-03-05', 'Frites maison', ['vegetarian', 'france']);
        $this->createDish($child, '2026-03-09', 'Frites', ['vegetarian']);
        $this->createDish($child, '2026-03-12', 'Frites', ['vegetarian']);
        $this->createDish($child, '2026-03-12', 'Steak', ['france']);

        $content = $this->callTool($tenant, 'search-dishes', [
            'tags' => ['vegetarian'],
            'start_date' => '2026-03-04',
            'order' => 'desc',
            'limit' => 2,
        ]);
        $this->assertSame(3, $content['total']);
        $this->assertSame(['2026-03-12', '2026-03-09'], array_column($content['results'], 'date'));

        $content = $this->callTool($tenant, 'search-dishes', ['tags' => ['vegetarian', 'france'], 'end_date' => '2026-03-10']);
        $this->assertSame(1, $content['total']);
        $this->assertSame('Frites maison', $content['results'][0]['name']);
    }

    public function test_search_dishes_treats_like_wildcards_literally(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-03-02', 'Frites');

        $this->assertSame(0, $this->callTool($tenant, 'search-dishes', ['query' => '%'])['total']);
    }

    public function test_search_dishes_rejects_unknown_tags(): void
    {
        $tenant = Tenant::factory()->create();

        $this->callToolError($tenant, 'search-dishes', ['tags' => ['spicy']]);
    }

    public function test_list_events_returns_upcoming_events_and_announcements_only(): void
    {
        $tenant = Tenant::factory()->create();
        Information::factory()->event('Nouvel an chinois')->create(['tenant_id' => $tenant->id, 'date' => '2026-02-17']);
        Information::factory()->event('Mardi gras')->create(['tenant_id' => $tenant->id, 'date' => '2026-03-10']);
        Information::factory()->create(['tenant_id' => $tenant->id, 'date' => '2026-03-12', 'information' => 'Fermé à 13h']);
        Information::factory()->create(['tenant_id' => $tenant->id, 'date' => '2026-03-13', 'event_name' => '']);

        $this->assertSame([
            'canteen' => $tenant->name,
            'events' => [
                ['date' => '2026-03-10', 'weekday' => 'mardi', 'event_name' => 'Mardi gras', 'information' => null],
                ['date' => '2026-03-12', 'weekday' => 'jeudi', 'event_name' => null, 'information' => 'Fermé à 13h'],
            ],
        ], $this->callTool($tenant, 'list-events'));

        $events = $this->callTool($tenant, 'list-events', ['start_date' => '2026-01-01', 'end_date' => '2026-02-28'])['events'];
        $this->assertSame(['Nouvel an chinois'], array_column($events, 'event_name'));
    }

    public function test_browsers_opening_the_mcp_url_land_on_the_docs(): void
    {
        $tenant = Tenant::factory()->create();

        $this->get(route('mcp', ['tenantSlug' => $tenant->slug]))
            ->assertRedirect(route('api-docs', ['tenantSlug' => $tenant->slug]).'#mcp');
    }

    public function test_mcp_clients_opening_an_sse_stream_still_get_a_405(): void
    {
        $tenant = Tenant::factory()->create();

        $this->get(route('mcp', ['tenantSlug' => $tenant->slug]), ['Accept' => 'text/event-stream'])
            ->assertStatus(405)
            ->assertHeader('Allow', 'POST');
    }

    public function test_the_mcp_server_and_the_api_have_separate_rate_limits(): void
    {
        $tenant = Tenant::factory()->create();

        for ($i = 0; $i < RouteServiceProvider::API_REQUESTS_PER_MINUTE; $i++) {
            $this->getJson(route('api.today', ['tenantSlug' => $tenant->slug]))->assertOk();
        }
        $this->getJson(route('api.today', ['tenantSlug' => $tenant->slug]))->assertTooManyRequests();

        $this->mcp($tenant, 'tools/list')
            ->assertOk()
            ->assertHeader('X-RateLimit-Limit', RouteServiceProvider::MCP_REQUESTS_PER_MINUTE);
    }
}
