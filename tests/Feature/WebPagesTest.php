<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Tests\Concerns\BuildsMenus;
use Tests\TestCase;

class WebPagesTest extends TestCase
{
    use BuildsMenus;

    public function test_the_home_page_lists_active_tenants_only(): void
    {
        $active = Tenant::factory()->create(['name' => 'Lille']);
        $inactive = Tenant::factory()->inactive()->create(['name' => 'Lens']);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Lille');
        $response->assertDontSee('Lens');
    }

    public function test_the_legal_page_renders(): void
    {
        $this->get(route('legal'))->assertOk();
    }

    public function test_the_menu_page_renders_the_dishes_of_the_week(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        // 2026-03-02 is a Monday.
        $this->createDish($child, '2026-03-02', 'Poulet rôti');

        $response = $this->get(route('menus', ['tenantSlug' => $tenant->slug, 'date' => '2026-03-02']));

        $response->assertOk();
        $response->assertSee('Poulet rôti', false);
    }

    public function test_the_dashboard_renders(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-03-02', 'Poulet rôti');

        $response = $this->get(route('dashboard', ['tenantSlug' => $tenant->slug, 'date' => '2026-03-02']));

        $response->assertOk();
        $response->assertSee('Poulet rôti', false);
    }

    public function test_the_notifications_page_renders(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-03-02', 'Poulet rôti');

        $this->get(route('notifications', ['tenantSlug' => $tenant->slug, 'date' => '2026-03-02']))
            ->assertOk();
    }

    public function test_the_webex_preview_renders(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-03-02', 'Poulet rôti');

        $this->get(route('notifications.webex', ['tenantSlug' => $tenant->slug, 'date' => '2026-03-02']))
            ->assertOk();
    }

    public function test_the_tenant_home_renders_the_menu(): void
    {
        $tenant = Tenant::factory()->create();
        $this->createCategoryTree($tenant);

        $this->get(route('tenant.home', ['tenantSlug' => $tenant->slug]))->assertOk();
    }

    public function test_the_legacy_routes_redirect_to_roubaix(): void
    {
        $this->get('/dashboard')->assertRedirect('/roubaix/dashboard');
        $this->get('/menus/2026-03-02')->assertRedirect('/roubaix/menus/2026-03-02');
        $this->get('/menu/2026-03-02')->assertRedirect('/roubaix/menus/2026-03-02');
        $this->get('/notifications')->assertRedirect('/roubaix/notifications');
    }

    public function test_the_account_page_requires_authentication(): void
    {
        $this->get(route('account'))->assertRedirect(route('login'));
    }

    public function test_the_admin_area_requires_authentication(): void
    {
        $this->get(route('admin'))->assertRedirect(route('login'));
        $this->get(route('admin.jobs'))->assertRedirect(route('login'));
    }
}
