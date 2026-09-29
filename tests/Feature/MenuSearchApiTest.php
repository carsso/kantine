<?php

namespace Tests\Feature;

use App\Models\Information;
use App\Models\Tenant;
use Tests\Concerns\BuildsMenus;
use Tests\TestCase;

class MenuSearchApiTest extends TestCase
{
    use BuildsMenus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-03-04 12:00:00');
    }

    public function test_the_api_index_lists_the_search_routes(): void
    {
        $tenant = Tenant::factory()->create();

        $this->getJson(route('api.home', ['tenantSlug' => $tenant->slug]))
            ->assertOk()
            ->assertJsonPath($tenant->slug.'.menus', route('api.menus', ['tenantSlug' => $tenant->slug]))
            ->assertJsonPath($tenant->slug.'.dishes', route('api.dishes', ['tenantSlug' => $tenant->slug]))
            ->assertJsonPath($tenant->slug.'.events', route('api.events', ['tenantSlug' => $tenant->slug]));
    }

    public function test_the_menus_endpoint_returns_the_requested_range(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-03-02', 'Couscous');
        $this->createDish($child, '2026-03-09', 'Lasagnes');

        $this->getJson(route('api.menus', ['tenantSlug' => $tenant->slug, 'start_date' => '2026-03-02', 'end_date' => '2026-03-06']))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.date', '2026-03-02')
            ->assertJsonPath('0.dishes.0.name', 'Couscous')
            ->assertJsonPath('0.dishes.0.category', 'Pole Chaud');
    }

    public function test_the_menus_endpoint_validates_the_range(): void
    {
        $tenant = Tenant::factory()->create();

        $this->getJson(route('api.menus', ['tenantSlug' => $tenant->slug, 'start_date' => '2026-03-01', 'end_date' => '2026-06-01']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('end_date');

        $this->getJson(route('api.menus', ['tenantSlug' => $tenant->slug, 'start_date' => '04/03/2026']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('start_date');
    }

    public function test_the_dishes_endpoint_searches_by_name_and_comma_separated_tags(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-03-02', 'Frites', ['vegetarian']);
        $this->createDish($child, '2026-03-05', 'Frites', ['vegetarian', 'france']);
        $this->createDish($child, '2026-03-06', 'Steak frites', ['france']);

        $this->getJson(route('api.dishes', ['tenantSlug' => $tenant->slug, 'query' => 'frites', 'tags' => 'vegetarian,france']))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('results.0.date', '2026-03-05');

        $this->getJson(route('api.dishes', ['tenantSlug' => $tenant->slug, 'query' => 'frites', 'start_date' => '2026-03-04']))
            ->assertOk()
            ->assertJsonPath('total', 2);
    }

    public function test_the_dishes_endpoint_validates_its_filters(): void
    {
        $tenant = Tenant::factory()->create();

        $this->getJson(route('api.dishes', ['tenantSlug' => $tenant->slug, 'tags' => 'spicy', 'limit' => 1000, 'order' => 'random']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['tags.0', 'limit', 'order']);
    }

    public function test_the_events_endpoint_returns_upcoming_events(): void
    {
        $tenant = Tenant::factory()->create();
        Information::factory()->event('Nouvel an chinois')->create(['tenant_id' => $tenant->id, 'date' => '2026-02-17']);
        Information::factory()->event('Mardi gras')->create(['tenant_id' => $tenant->id, 'date' => '2026-03-10']);

        $this->getJson(route('api.events', ['tenantSlug' => $tenant->slug]))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.event_name', 'Mardi gras');
    }

    public function test_the_search_endpoints_hide_inactive_tenants(): void
    {
        $tenant = Tenant::factory()->inactive()->create();

        $this->getJson(route('api.dishes', ['tenantSlug' => $tenant->slug]))->assertNotFound();
        $this->getJson(route('api.menus', ['tenantSlug' => $tenant->slug]))->assertNotFound();
        $this->getJson(route('api.events', ['tenantSlug' => $tenant->slug]))->assertNotFound();
    }
}
