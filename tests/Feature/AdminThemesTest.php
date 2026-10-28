<?php

namespace Tests\Feature;

use App\Enums\DashboardStyleMode;
use App\Events\DashboardRefreshEvent;
use App\Models\CalendarEvent;
use App\Models\Dish;
use App\Models\Information;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantRolesAndPermissionsService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AdminThemesTest extends TestCase
{
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create(['name' => 'Lille']);

        app(TenantRolesAndPermissionsService::class)->createTenantRolesAndPermissions();
    }

    private function actingAsTenantAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Tenant Admin '.$this->tenant->slug);
        $this->actingAs($user);

        return $user;
    }

    public function test_the_calendar_page_requires_the_tenant_permission(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.themes', ['tenantSlug' => $this->tenant->slug]))->assertForbidden();
    }

    public function test_the_calendar_page_lists_the_dates_of_the_year(): void
    {
        $this->actingAsTenantAdmin();

        $this->get(route('admin.themes', ['tenantSlug' => $this->tenant->slug, 'year' => 2026]))
            ->assertOk()
            ->assertSee('Calendrier des thèmes 2026')
            ->assertSeeInOrder(['lundi 2 février', 'Chandeleur', 'Crêpes (Chandeleur)'])
            ->assertSeeInOrder(['du 2 avril au 6 avril', 'Pâques'])
            ->assertSeeInOrder(['du 1er mars au 3 mars', 'Printemps météorologique', 'Saint-Patrick', 'Pâques'])
            ->assertDontSee('Printemps astronomique');
    }

    public function test_the_calendar_page_colors_each_day_with_its_style(): void
    {
        $this->actingAsTenantAdmin();

        $this->get(route('admin.themes', ['tenantSlug' => $this->tenant->slug, 'year' => 2026]))
            ->assertOk()
            ->assertSee('title="lundi 2 février : Chandeleur (lundi 2 février) - Crêpes (Chandeleur)"', false)
            ->assertSee('title="mercredi 1er avril : Poisson d&#039;avril (mercredi 1er avril) - Poisson d&#039;avril"', false)
            ->assertSee('title="vendredi 13 février : Saint-Valentin (samedi 14 février) - Saint-Valentin"', false)
            ->assertSee('title="mardi 12 mai : pas de style"', false)
            ->assertSee('title="vendredi 1er mai (férié : Journée internationale des travailleurs) : Journée internationale des travailleurs (vendredi 1er mai) - Journée internationale des travailleurs"', false)
            ->assertSee('title="lundi 25 mai (journée de solidarité : Lundi de Pentecôte) : pas de style"', false)
            ->assertSee('title="lundi 6 avril (férié : Lundi de Pâques) : Pâques (dimanche 5 avril) - Pâques"', false)
            ->assertSeeInOrder(['Légende', 'Nouvel an', ': jeudi 1er janvier, vendredi 1er janvier 2027', 'Galette des rois', ': mardi 6 janvier', 'Crêpes (Chandeleur)', 'Chandeleur : lundi 2 février', 'Pas de style'])
            ->assertDontSee('Galette des rois : mardi 6 janvier');
    }

    public function test_the_calendar_page_shows_the_choices_made_in_the_menus(): void
    {
        $this->actingAsTenantAdmin();
        Information::factory()->create(['tenant_id' => $this->tenant->id, 'date' => '2026-12-22', 'style' => 'snow']);
        Information::factory()->create(['tenant_id' => $this->tenant->id, 'date' => '2026-10-12', 'event_name' => 'Tartiflette']);
        Information::factory()->create(['tenant_id' => $this->tenant->id, 'date' => '2026-02-02', 'style' => 'none']);
        Information::factory()->create(['date' => '2026-05-12', 'style' => 'snow']);
        Dish::factory()->create(['tenant_id' => $this->tenant->id, 'date' => '2026-10-19', 'name' => 'Burger poisson, frites']);
        Dish::factory()->create(['tenant_id' => $this->tenant->id, 'date' => '2026-10-19', 'name' => 'Burger végétarien, frites']);
        Dish::factory()->create(['tenant_id' => $this->tenant->id, 'date' => '2026-10-13', 'name' => 'Burger poisson, frites']);

        $this->get(route('admin.themes', ['tenantSlug' => $this->tenant->slug, 'year' => 2026]))
            ->assertOk()
            ->assertSee('title="mardi 22 décembre : style choisi dans le menu - Neige (à la place de : Noël)"', false)
            ->assertSee('title="lundi 12 octobre : Animation « Tartiflette » - Montagne (raclette, fondue, tartiflette)"', false)
            ->assertSee('title="lundi 2 février : aucun style choisi dans le menu (à la place de : Chandeleur)"', false)
            ->assertSee('title="mardi 12 mai : pas de style"', false)
            ->assertSee('Choisi dans le menu : mardi 22 décembre')
            ->assertSee('Animation « Tartiflette » : lundi 12 octobre')
            ->assertSee('title="mardi 13 octobre : Jour des Burgers et des Frites - Burger"', false)
            ->assertSee('Jour des Burgers et des Frites : mardi 13 octobre')
            ->assertSee('title="lundi 19 octobre : Octobre rose - Octobre rose"', false);
    }

    public function test_the_calendar_page_follows_the_mode_of_the_tenant(): void
    {
        $this->actingAsTenantAdmin();
        Information::factory()->create(['tenant_id' => $this->tenant->id, 'date' => '2026-12-21', 'style' => 'snow']);
        $this->tenant->update(['dashboard_style_mode' => DashboardStyleMode::Manual]);

        $this->get(route('admin.themes', ['tenantSlug' => $this->tenant->slug, 'year' => 2026]))
            ->assertSee('title="lundi 21 décembre : style choisi dans le menu - Neige"', false)
            ->assertSee('title="lundi 2 février : pas de style"', false);

        $this->tenant->update(['dashboard_style_mode' => DashboardStyleMode::Disabled]);

        $this->get(route('admin.themes', ['tenantSlug' => $this->tenant->slug, 'year' => 2026]))
            ->assertSee('title="lundi 21 décembre : pas de style"', false);
    }

    public function test_the_calendar_page_shows_where_periods_overlap(): void
    {
        $this->actingAsTenantAdmin();

        $this->get(route('admin.themes', ['tenantSlug' => $this->tenant->slug, 'year' => 2026]))
            ->assertOk()
            ->assertSeeInOrder(['Mois des fiertés', 'Feux de la Saint-Jean le 24 juin'], false);
    }

    public function test_a_period_only_yields_to_other_periods(): void
    {
        $this->actingAsTenantAdmin();

        $this->get(route('admin.themes', ['tenantSlug' => $this->tenant->slug, 'year' => 2026]))
            ->assertOk()
            ->assertDontSee('Chandeleur le 2 février')
            ->assertDontSee('Noël le 21 décembre');
    }

    public function test_the_calendar_page_follows_the_movable_festivities(): void
    {
        $this->actingAsTenantAdmin();

        $this->get(route('admin.themes', ['tenantSlug' => $this->tenant->slug, 'year' => 2027]))
            ->assertOk()
            ->assertSeeInOrder(['du 25 mars au 29 mars', 'Pâques']);
    }

    public function test_a_tenant_admin_can_switch_off_the_styles(): void
    {
        Event::fake([DashboardRefreshEvent::class]);
        $this->actingAsTenantAdmin();

        $this->from(route('admin.themes', ['tenantSlug' => $this->tenant->slug]))
            ->post(route('admin.themes.mode', ['tenantSlug' => $this->tenant->slug]), ['dashboard_style_mode' => 'disabled'])
            ->assertRedirect(route('admin.themes', ['tenantSlug' => $this->tenant->slug]));

        $this->assertSame(DashboardStyleMode::Disabled, $this->tenant->fresh()->dashboard_style_mode);
        Event::assertDispatched(DashboardRefreshEvent::class);
    }

    public function test_the_mode_switch_is_only_on_the_calendar_page(): void
    {
        $this->tenant->update(['dashboard_style_mode' => DashboardStyleMode::Disabled]);
        $this->actingAsTenantAdmin();

        $this->get(route('admin'))
            ->assertOk()
            ->assertSee(route('admin.themes', ['tenantSlug' => $this->tenant->slug]))
            ->assertDontSee(route('admin.themes.mode', ['tenantSlug' => $this->tenant->slug]));

        $this->get(route('admin.themes', ['tenantSlug' => $this->tenant->slug]))
            ->assertOk()
            ->assertSee(route('admin.themes.mode', ['tenantSlug' => $this->tenant->slug]))
            ->assertSee('<option value="disabled" selected', false)
            ->assertSee('Aucun style n\'est affiché sur les dashboards de cette cantine', false);
    }

    public function test_a_tenant_admin_can_add_an_event_to_the_calendar(): void
    {
        $this->actingAsTenantAdmin();

        $this->post(route('admin.themes.events.store', ['tenantSlug' => $this->tenant->slug]), [
            'label' => 'Coupe du monde 2026',
            'style' => 'world_cup',
            'starts_on' => '2026-06-11',
            'ends_on' => '2026-07-19',
        ])->assertRedirect(route('admin.themes', ['tenantSlug' => $this->tenant->slug, 'year' => 2026]));

        $this->assertDatabaseHas('calendar_events', ['tenant_id' => $this->tenant->id, 'label' => 'Coupe du monde 2026', 'style' => 'world_cup']);
        $this->get(route('admin.themes', ['tenantSlug' => $this->tenant->slug, 'year' => 2026]))
            ->assertSee('title="vendredi 12 juin : Coupe du monde 2026 (du 11 juin au 19 juillet) - Coupe du monde / Euro"', false)
            ->assertSee('title="mardi 14 juillet (férié : Fête nationale) : Coupe du monde 2026 (du 11 juin au 19 juillet) - Coupe du monde / Euro"', false);
    }

    public function test_the_event_form_uses_the_style_gallery(): void
    {
        $this->actingAsTenantAdmin();

        $this->get(route('admin.themes', ['tenantSlug' => $this->tenant->slug]))
            ->assertOk()
            ->assertSee('<script type="application/json" id="particle-themes">', false)
            ->assertSee('<style-picker name="style" value="" preview-url="'.route('dashboard', ['tenantSlug' => $this->tenant->slug]).'" themes-only>', false);
    }

    public function test_an_event_needs_a_known_style_and_ordered_dates(): void
    {
        $this->actingAsTenantAdmin();

        $this->post(route('admin.themes.events.store', ['tenantSlug' => $this->tenant->slug]), [
            'label' => 'Disco',
            'style' => 'disco',
            'starts_on' => '2026-06-11',
            'ends_on' => '2026-06-01',
        ])->assertSessionHasErrors(['style', 'ends_on']);

        $this->assertDatabaseCount('calendar_events', 0);
    }

    public function test_the_edit_link_fills_the_form_with_the_event(): void
    {
        $this->actingAsTenantAdmin();
        $event = CalendarEvent::factory()->create(['tenant_id' => $this->tenant->id, 'label' => 'Roland-Garros 2026', 'style' => 'tennis', 'starts_on' => '2026-05-24', 'ends_on' => '2026-06-07']);

        $this->get(route('admin.themes', ['tenantSlug' => $this->tenant->slug, 'year' => 2026, 'edit' => $event->id]))
            ->assertOk()
            ->assertSee('action="'.route('admin.themes.events.update', ['tenantSlug' => $this->tenant->slug, 'calendarEvent' => $event->id]).'"', false)
            ->assertSee('name="_method" value="PUT"', false)
            ->assertSee('value="Roland-Garros 2026"', false)
            ->assertSee('<style-picker name="style" value="tennis"', false)
            ->assertSee('value="2026-05-24"', false)
            ->assertSee('value="2026-06-07"', false)
            ->assertSee('Enregistrer');
    }

    public function test_a_tenant_admin_can_update_an_event(): void
    {
        $this->actingAsTenantAdmin();
        $event = CalendarEvent::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->put(route('admin.themes.events.update', ['tenantSlug' => $this->tenant->slug, 'calendarEvent' => $event->id]), [
            'label' => 'Euro 2028',
            'style' => 'world_cup',
            'starts_on' => '2028-06-09',
            'ends_on' => '2028-07-09',
        ])->assertRedirect(route('admin.themes', ['tenantSlug' => $this->tenant->slug, 'year' => 2028]));

        $event->refresh();
        $this->assertSame('Euro 2028', $event->label);
        $this->assertSame('2028-06-09', $event->starts_on->format('Y-m-d'));
    }

    public function test_an_event_of_another_tenant_cannot_be_updated(): void
    {
        $this->actingAsTenantAdmin();
        $event = CalendarEvent::factory()->create(['label' => 'Coupe du monde']);

        $this->put(route('admin.themes.events.update', ['tenantSlug' => $this->tenant->slug, 'calendarEvent' => $event->id]), [
            'label' => 'Piraté',
            'style' => 'world_cup',
            'starts_on' => '2026-06-11',
            'ends_on' => '2026-07-19',
        ])->assertNotFound();

        $this->assertSame('Coupe du monde', $event->fresh()->label);
    }

    public function test_a_tenant_admin_can_delete_an_event(): void
    {
        $this->actingAsTenantAdmin();
        $event = CalendarEvent::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->delete(route('admin.themes.events.destroy', ['tenantSlug' => $this->tenant->slug, 'calendarEvent' => $event->id]))
            ->assertRedirect();

        $this->assertModelMissing($event);
    }

    public function test_an_event_of_another_tenant_cannot_be_deleted(): void
    {
        $this->actingAsTenantAdmin();
        $event = CalendarEvent::factory()->create();

        $this->delete(route('admin.themes.events.destroy', ['tenantSlug' => $this->tenant->slug, 'calendarEvent' => $event->id]))
            ->assertNotFound();

        $this->assertModelExists($event);
    }

    public function test_it_rejects_an_unknown_mode(): void
    {
        $this->actingAsTenantAdmin();

        $this->post(route('admin.themes.mode', ['tenantSlug' => $this->tenant->slug]), ['dashboard_style_mode' => 'disco'])
            ->assertSessionHasErrors('dashboard_style_mode');

        $this->assertSame(DashboardStyleMode::Automatic, $this->tenant->fresh()->dashboard_style_mode);
    }

    public function test_switching_the_mode_requires_the_tenant_permission(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('admin.themes.mode', ['tenantSlug' => $this->tenant->slug]), ['dashboard_style_mode' => 'disabled'])
            ->assertForbidden();

        $this->assertSame(DashboardStyleMode::Automatic, $this->tenant->fresh()->dashboard_style_mode);
    }
}
