<?php

namespace Tests\Feature;

use App\Events\MenuUpdatedEvent;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\Information;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantRolesAndPermissionsService;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\BuildsMenus;
use Tests\TestCase;

class ApiAdminMenuTest extends TestCase
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

    private function actingAsTenantAdmin(?Tenant $tenant = null): User
    {
        $user = User::factory()->create();
        $user->assignRole('Tenant Admin '.($tenant ?? $this->tenant)->slug);
        Sanctum::actingAs($user);

        return $user;
    }

    private function menuUrl(string $date = '2026-03-02'): string
    {
        return route('api.admin.menus.update', ['tenantSlug' => $this->tenant->slug, 'date' => $date]);
    }

    /**
     * @param  array<int, array{name: string, tags?: array<int, string>}>  $dishes
     * @return array<string, mixed>
     */
    private function payload(array $dishes): array
    {
        return [
            'dishes' => [
                'mains' => [
                    'pole-chaud' => [
                        'plats' => $dishes,
                    ],
                ],
            ],
        ];
    }

    public function test_it_requires_authentication(): void
    {
        $this->postJson($this->menuUrl(), $this->payload([['name' => 'Poulet']]))
            ->assertUnauthorized();
    }

    public function test_it_rejects_a_user_without_the_tenant_permission(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson($this->menuUrl(), $this->payload([['name' => 'Poulet']]))
            ->assertForbidden();
    }

    public function test_an_admin_of_another_tenant_is_rejected(): void
    {
        $otherTenant = Tenant::factory()->create(['name' => 'Lens']);
        app(TenantRolesAndPermissionsService::class)->createTenantRolesAndPermissions();
        $this->actingAsTenantAdmin($otherTenant);

        $this->postJson($this->menuUrl(), $this->payload([['name' => 'Poulet']]))
            ->assertForbidden();
    }

    public function test_a_tenant_admin_can_read_the_menu(): void
    {
        $this->actingAsTenantAdmin();
        $this->createDish($this->category, '2026-03-02', 'Poulet rôti');

        $response = $this->getJson(route('api.admin.menus.get', [
            'tenantSlug' => $this->tenant->slug,
            'date' => '2026-03-02',
        ]));

        $response->assertOk();
        $response->assertJsonPath('dishes.mains.pole-chaud.plats.0.name', 'Poulet rôti');
    }

    public function test_it_creates_the_dishes_of_the_day(): void
    {
        Event::fake([MenuUpdatedEvent::class]);
        $this->actingAsTenantAdmin();

        $response = $this->postJson($this->menuUrl(), $this->payload([
            ['name' => 'Poulet rôti', 'tags' => ['halal']],
            ['name' => 'Gratin de courgettes', 'tags' => ['vegetarian', 'organic']],
        ]));

        $response->assertOk();
        $response->assertJsonPath('message', 'Menu mis à jour avec succès');

        $this->assertDatabaseCount('dishes', 2);
        $this->assertDatabaseHas('dishes', [
            'tenant_id' => $this->tenant->id,
            'date' => '2026-03-02',
            'name' => 'Poulet rôti',
            'dishes_category_id' => $this->category->id,
        ]);
        $this->assertSame(['vegetarian', 'organic'], Dish::where('name', 'Gratin de courgettes')->first()->tags);

        Event::assertDispatched(MenuUpdatedEvent::class);
    }

    public function test_it_removes_dishes_that_are_no_longer_present(): void
    {
        Event::fake([MenuUpdatedEvent::class]);
        $this->actingAsTenantAdmin();
        $this->createDish($this->category, '2026-03-02', 'Ancien plat');

        $this->postJson($this->menuUrl(), $this->payload([['name' => 'Nouveau plat']]))->assertOk();

        $this->assertDatabaseMissing('dishes', ['name' => 'Ancien plat']);
        $this->assertDatabaseHas('dishes', ['name' => 'Nouveau plat']);
    }

    public function test_it_leaves_the_dishes_of_another_day_untouched(): void
    {
        Event::fake([MenuUpdatedEvent::class]);
        $this->actingAsTenantAdmin();
        $this->createDish($this->category, '2026-03-03', 'Plat du lendemain');

        $this->postJson($this->menuUrl(), $this->payload([['name' => 'Plat du jour']]))->assertOk();

        $this->assertDatabaseHas('dishes', ['name' => 'Plat du lendemain']);
    }

    public function test_it_stores_the_information_of_the_day(): void
    {
        Event::fake([MenuUpdatedEvent::class]);
        $this->actingAsTenantAdmin();

        $payload = $this->payload([['name' => 'Poulet']]);
        $payload['information'] = [
            'event_name' => 'Semaine du goût',
            'information' => 'Repas offert',
        ];

        $this->postJson($this->menuUrl(), $payload)->assertOk();

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

        $response = $this->postJson(
            route('api.admin.menus.update', ['tenantSlug' => $this->tenant->slug, 'date' => '02-03-2026']),
            $this->payload([['name' => 'Poulet']])
        );

        $response->assertStatus(422);
        $response->assertJsonPath('error', 'Format de date invalide');
    }

    public function test_it_rejects_an_unknown_category(): void
    {
        $this->actingAsTenantAdmin();

        $response = $this->postJson($this->menuUrl(), [
            'dishes' => [
                'mains' => [
                    'categorie-inconnue' => [
                        'plats' => [['name' => 'Poulet']],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error', 'Catégories invalides');
        $response->assertJsonPath('invalid_categories', ['mains.categorie-inconnue.plats']);
        $this->assertDatabaseCount('dishes', 0);
    }

    public function test_it_rejects_an_unknown_tag(): void
    {
        $this->actingAsTenantAdmin();

        $response = $this->postJson($this->menuUrl(), $this->payload([
            ['name' => 'Poulet', 'tags' => ['delicieux']],
        ]));

        $response->assertStatus(422);
        $response->assertJsonPath('error', 'Tags invalides');
        $response->assertJsonPath('invalid_tags', ['delicieux']);
        $this->assertDatabaseCount('dishes', 0);
    }

    public function test_it_rejects_an_unknown_style(): void
    {
        $this->actingAsTenantAdmin();

        $payload = $this->payload([['name' => 'Poulet']]);
        $payload['information'] = ['style' => 'disco'];

        $response = $this->postJson($this->menuUrl(), $payload);

        $response->assertStatus(422);
        $response->assertJsonPath('error', 'Style invalide');
        $this->assertDatabaseCount('dishes', 0);
    }

    public function test_it_rejects_a_payload_without_dishes(): void
    {
        $this->actingAsTenantAdmin();

        $this->postJson($this->menuUrl(), [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('dishes');
    }

    public function test_it_rejects_a_dish_without_a_name(): void
    {
        $this->actingAsTenantAdmin();

        $this->postJson($this->menuUrl(), $this->payload([['tags' => ['halal']]]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('dishes.mains.pole-chaud.plats.0.name');
    }

    public function test_it_does_not_accept_a_category_belonging_to_another_tenant(): void
    {
        $this->actingAsTenantAdmin();
        $otherTenant = Tenant::factory()->create(['name' => 'Lens']);
        $this->createCategoryTree($otherTenant, 'mains', 'Pole Voisin', 'Plats Voisins');

        $response = $this->postJson($this->menuUrl(), [
            'dishes' => [
                'mains' => [
                    'pole-voisin' => [
                        'plats-voisins' => [['name' => 'Poulet']],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error', 'Catégories invalides');
    }

    public function test_posting_the_same_menu_twice_is_idempotent(): void
    {
        Event::fake([MenuUpdatedEvent::class]);
        $this->actingAsTenantAdmin();

        $payload = $this->payload([['name' => 'Poulet rôti', 'tags' => ['halal']]]);
        $payload['information'] = ['event_name' => 'Semaine du goût'];

        $this->postJson($this->menuUrl(), $payload)->assertOk();
        $this->postJson($this->menuUrl(), $payload)->assertOk();

        $this->assertDatabaseCount('dishes', 1);
        $this->assertSame(1, Information::where('tenant_id', $this->tenant->id)->count());
    }

    public function test_it_does_not_create_an_information_row_when_none_is_posted(): void
    {
        Event::fake([MenuUpdatedEvent::class]);
        $this->actingAsTenantAdmin();

        $this->postJson($this->menuUrl(), $this->payload([['name' => 'Poulet']]))->assertOk();

        $this->assertDatabaseCount('informations', 0);
    }
}
