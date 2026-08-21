<?php

namespace Tests\Feature;

use App\Events\MenuUpdatedEvent;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantRolesAndPermissionsService;
use Illuminate\Support\Facades\Event;
use Tests\Concerns\BuildsMenus;
use Tests\TestCase;

class AdminMenuFormTest extends TestCase
{
    use BuildsMenus;

    private Tenant $tenant;

    private DishCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create(['name' => 'Lille']);
        [, $this->category] = $this->createCategoryTree($this->tenant);

        app(TenantRolesAndPermissionsService::class)->createTenantRolesAndPermissions();
    }

    private function actingAsTenantAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Tenant Admin '.$this->tenant->slug);
        $this->actingAs($user);

        return $user;
    }

    /**
     * @param  array<int, string>  $dishNames
     * @return array<string, mixed>
     */
    private function formPayload(array $dishNames, string $date = '2026-03-02'): array
    {
        return [
            'date' => [$date => $date],
            'event_name' => [$date => null],
            'information' => [$date => null],
            'style' => [$date => null],
            'dishes' => [$date => [$this->category->id => $dishNames]],
        ];
    }

    public function test_the_admin_menu_page_requires_the_tenant_permission(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.menu', ['tenantSlug' => $this->tenant->slug]))->assertForbidden();
    }

    public function test_a_tenant_admin_can_open_the_menu_editor(): void
    {
        $this->actingAsTenantAdmin();

        $this->get(route('admin.menu', ['tenantSlug' => $this->tenant->slug, 'date' => '2026-03-02']))
            ->assertOk();
    }

    public function test_it_creates_the_dishes_of_the_form(): void
    {
        Event::fake([MenuUpdatedEvent::class]);
        $this->actingAsTenantAdmin();

        $this->post(route('admin.menu.update', ['tenantSlug' => $this->tenant->slug]), $this->formPayload([
            'poulet rôti',
            'gratin de courgettes',
        ]))->assertRedirect();

        // Names are capitalised before being stored.
        $this->assertDatabaseHas('dishes', ['name' => 'Poulet rôti', 'date' => '2026-03-02']);
        $this->assertDatabaseHas('dishes', ['name' => 'Gratin de courgettes']);
        Event::assertDispatched(MenuUpdatedEvent::class);
    }

    public function test_it_drops_the_empty_rows_of_the_form(): void
    {
        Event::fake([MenuUpdatedEvent::class]);
        $this->actingAsTenantAdmin();

        $this->post(route('admin.menu.update', ['tenantSlug' => $this->tenant->slug]), $this->formPayload([
            'Poulet rôti',
            '',
            null,
        ]))->assertRedirect();

        $this->assertDatabaseCount('dishes', 1);
    }

    public function test_it_stores_the_tags_of_a_dish(): void
    {
        Event::fake([MenuUpdatedEvent::class]);
        $this->actingAsTenantAdmin();

        $payload = $this->formPayload(['Poulet rôti']);
        $payload['dishes_tags'] = ['2026-03-02' => [$this->category->id => ['halal,france']]];

        $this->post(route('admin.menu.update', ['tenantSlug' => $this->tenant->slug]), $payload)
            ->assertRedirect();

        $this->assertSame(['halal', 'france'], Dish::where('name', 'Poulet rôti')->first()->tags);
    }

    public function test_it_removes_the_dishes_that_were_deleted_from_the_form(): void
    {
        Event::fake([MenuUpdatedEvent::class]);
        $this->actingAsTenantAdmin();
        $this->createDish($this->category, '2026-03-02', 'Ancien plat');

        $this->post(route('admin.menu.update', ['tenantSlug' => $this->tenant->slug]), $this->formPayload([
            'Nouveau plat',
        ]))->assertRedirect();

        $this->assertDatabaseMissing('dishes', ['name' => 'Ancien plat']);
    }

    public function test_it_stores_the_information_of_the_day(): void
    {
        Event::fake([MenuUpdatedEvent::class]);
        $this->actingAsTenantAdmin();

        $payload = $this->formPayload(['Poulet rôti']);
        $payload['event_name'] = ['2026-03-02' => 'Semaine du goût'];
        $payload['information'] = ['2026-03-02' => 'Repas offert'];

        $this->post(route('admin.menu.update', ['tenantSlug' => $this->tenant->slug]), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('informations', [
            'tenant_id' => $this->tenant->id,
            'date' => '2026-03-02',
            'event_name' => 'Semaine du goût',
            'information' => 'Repas offert',
        ]);
    }

    public function test_it_rejects_a_malformed_date(): void
    {
        $this->actingAsTenantAdmin();

        $payload = $this->formPayload(['Poulet rôti']);
        $payload['date'] = ['02-03-2026' => '02-03-2026'];

        $this->post(route('admin.menu.update', ['tenantSlug' => $this->tenant->slug]), $payload)
            ->assertSessionHasErrors('date.02-03-2026');
    }

    public function test_it_rejects_a_payload_without_dishes(): void
    {
        $this->actingAsTenantAdmin();

        $this->post(route('admin.menu.update', ['tenantSlug' => $this->tenant->slug]), [])
            ->assertSessionHasErrors(['date', 'dishes']);
    }
}
