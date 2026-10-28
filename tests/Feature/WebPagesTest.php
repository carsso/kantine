<?php

namespace Tests\Feature;

use App\Enums\DashboardStyleMode;
use App\Lib\ParticleThemes;
use App\Models\CalendarEvent;
use App\Models\Information;
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
        $this->createDish($child, '2026-03-02', 'Poulet rôti', ['halal'], ['gluten', 'celery']);
        $this->createDish($child, '2026-03-02', 'Pain', [], ['gluten']);

        $response = $this->get(route('menus', ['tenantSlug' => $tenant->slug, 'date' => '2026-03-02']));

        $response->assertOk();
        $response->assertSee('Poulet rôti', false);
        $response->assertSee('(Halal - <i>Allergènes : Gluten, Céleri</i>)', false);
        $response->assertSee('(<i>Allergènes : Gluten</i>)', false);
    }

    public function test_the_dashboard_renders(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-03-02', 'Poulet rôti', ['halal'], ['gluten', 'celery']);

        $response = $this->get(route('dashboard', ['tenantSlug' => $tenant->slug, 'date' => '2026-03-02']));

        $response->assertOk();
        $response->assertSee('Poulet rôti', false);
        $response->assertSee('(Halal - <i class="text-3xl">Allergènes : Gluten, Céleri</i>)', false);
        $response->assertSee(config('app.name').' · ', false);
    }

    public function test_the_event_name_is_shown_without_a_title(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-02-02', 'Crêpe au sucre');
        Information::factory()->event('Chandeleur')->create(['tenant_id' => $tenant->id, 'date' => '2026-02-02']);
        $params = ['tenantSlug' => $tenant->slug, 'date' => '2026-02-02'];

        foreach (['dashboard', 'menus', 'notifications.webex'] as $routeName) {
            $response = $this->get(route($routeName, $params));

            $response->assertOk();
            $response->assertSee('Chandeleur');
            $response->assertDontSee('Événement');
        }
    }

    public function test_the_dashboard_renders_every_style(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-03-02', 'Poulet rôti');

        foreach (config('tsparticles.config') as $style => $options) {
            $response = $this->get(route('dashboard', ['tenantSlug' => $tenant->slug, 'date' => '2026-03-02', 'style' => $style]));

            $this->assertContains($options['category'] ?? null, ParticleThemes::CATEGORIES, "Style {$style} has no known category");
            $response->assertOk();
            $response->assertSee('<vue-particles', false);
            $response->assertSee(substr(json_encode($options['name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT), 1, -1), false);
        }
    }

    public function test_the_dashboard_picks_the_style_of_the_event(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-03-02', 'Tartiflette');
        Information::factory()->event('Tartiflette')->create(['tenant_id' => $tenant->id, 'date' => '2026-03-02']);

        $this->get(route('dashboard', ['tenantSlug' => $tenant->slug, 'date' => '2026-03-02']))
            ->assertOk()
            ->assertSee('"name":"Montagne (raclette, fondue, tartiflette)"', false);
    }

    public function test_the_dashboard_picks_the_style_of_the_calendar(): void
    {
        $tenant = Tenant::factory()->create();

        $this->get(route('dashboard', ['tenantSlug' => $tenant->slug, 'date' => '2026-10-29']))
            ->assertOk()
            ->assertSee('"name":"Halloween"', false)
            ->assertSeeInOrder(['Samedi 31 octobre :', 'Halloween']);
    }

    public function test_the_dashboard_shows_burgers_on_the_burgers_and_fries_day(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-10-12', 'Burger de saumon, frites, salade');

        $this->get(route('dashboard', ['tenantSlug' => $tenant->slug, 'date' => '2026-10-12']))
            ->assertOk()
            ->assertSee('"name":"Burger"', false)
            ->assertSee('Jour des Burgers et des Frites');
    }

    public function test_a_burger_without_fries_shows_no_style(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-10-12', 'Burger de saumon, salade');

        $this->get(route('dashboard', ['tenantSlug' => $tenant->slug, 'date' => '2026-10-12']))
            ->assertOk()
            ->assertDontSee('<vue-particles', false);
    }

    public function test_the_dashboard_picks_the_style_of_an_added_event(): void
    {
        $tenant = Tenant::factory()->create();
        CalendarEvent::factory()->create(['tenant_id' => $tenant->id, 'style' => 'tennis', 'starts_on' => '2026-10-26', 'ends_on' => '2026-10-30']);

        $this->get(route('dashboard', ['tenantSlug' => $tenant->slug, 'date' => '2026-10-29']))
            ->assertOk()
            ->assertSee('"name":"Roland-Garros"', false);
    }

    public function test_the_dashboard_can_disable_the_automatic_style(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-10-29', 'Poulet rôti');
        Information::factory()->create(['tenant_id' => $tenant->id, 'date' => '2026-10-29', 'style' => 'none']);

        $this->get(route('dashboard', ['tenantSlug' => $tenant->slug, 'date' => '2026-10-29']))
            ->assertOk()
            ->assertDontSee('<vue-particles', false);
    }

    public function test_the_tenant_can_switch_off_every_style(): void
    {
        $tenant = Tenant::factory()->create(['dashboard_style_mode' => DashboardStyleMode::Disabled]);
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-10-29', 'Poulet rôti');
        Information::factory()->create(['tenant_id' => $tenant->id, 'date' => '2026-10-29', 'style' => 'snow']);

        $this->get(route('dashboard', ['tenantSlug' => $tenant->slug, 'date' => '2026-10-29']))
            ->assertOk()
            ->assertDontSee('<vue-particles', false);
    }

    public function test_the_dashboard_ignores_an_unknown_style(): void
    {
        $tenant = Tenant::factory()->create();
        [, $child] = $this->createCategoryTree($tenant);
        $this->createDish($child, '2026-03-02', 'Poulet rôti');

        $this->get(route('dashboard', ['tenantSlug' => $tenant->slug, 'date' => '2026-03-02', 'style' => 'disco']))
            ->assertOk()
            ->assertDontSee('<vue-particles', false);
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
        $this->createDish($child, '2026-03-02', 'Poulet rôti', ['halal', 'france'], ['gluten']);

        $this->get(route('notifications.webex', ['tenantSlug' => $tenant->slug, 'date' => '2026-03-02']))
            ->assertOk()
            ->assertSee('(Halal, Origine France - <i>Allergènes : Gluten</i>)', false);
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
