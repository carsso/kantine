<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Tests\Concerns\BuildsMenus;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use BuildsMenus;

    public function test_the_api_index_lists_the_routes_of_every_active_tenant(): void
    {
        $active = Tenant::factory()->create(['name' => 'Lille']);
        $inactive = Tenant::factory()->inactive()->create(['name' => 'Lens']);

        $response = $this->getJson(route('api.home', ['tenantSlug' => $active->slug]));

        $response->assertOk();
        $response->assertJsonPath($active->slug.'.today', route('api.today', ['tenantSlug' => $active->slug]));
        $response->assertJsonMissingPath($inactive->slug);
    }

    public function test_the_day_endpoint_returns_the_menu_of_the_requested_date(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-03-02', 'Poulet rôti', ['halal']);

        $response = $this->getJson(route('api.day', ['tenantSlug' => $tenant->slug, 'date' => '2026-03-02']));

        $response->assertOk();
        $response->assertJsonPath('date', '2026-03-02');
        $response->assertJsonPath('dishes.mains.pole-chaud.plats.0.name', 'Poulet rôti');
        $response->assertJsonPath('dishes.mains.pole-chaud.plats.0.tags', ['halal']);
    }

    public function test_the_today_endpoint_returns_today(): void
    {
        $tenant = Tenant::factory()->create();

        $response = $this->getJson(route('api.today', ['tenantSlug' => $tenant->slug]));

        $response->assertOk();
        $response->assertJsonPath('date', now()->format('Y-m-d'));
    }

    public function test_the_api_never_exposes_the_webex_bearer_token(): void
    {
        $tenant = Tenant::factory()->withWebex()->create();

        $response = $this->getJson(route('api.today', ['tenantSlug' => $tenant->slug]));

        $response->assertOk();
        $response->assertJsonMissingPath('tenant.webex_bearer_token');
        $this->assertStringNotContainsString('webex-token', $response->getContent());
    }

    public function test_an_unknown_tenant_returns_404(): void
    {
        $this->getJson('/api/does-not-exist/today')->assertNotFound();
    }

    public function test_an_inactive_tenant_returns_404(): void
    {
        $tenant = Tenant::factory()->inactive()->create();

        $this->getJson(route('api.today', ['tenantSlug' => $tenant->slug]))->assertNotFound();
    }

    public function test_the_legacy_root_routes_redirect_to_roubaix(): void
    {
        $this->get('/api')->assertRedirect('/api/roubaix');
        $this->get('/api/today')->assertRedirect('/api/roubaix/today');
        $this->get('/api/day/2026-03-02')->assertRedirect('/api/roubaix/day/2026-03-02');
    }

    public function test_the_user_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }
}
