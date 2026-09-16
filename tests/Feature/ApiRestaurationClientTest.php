<?php

namespace Tests\Feature;

use App\Events\MenuUpdatedEvent;
use App\Lib\ApiRestaurationClient;
use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\Tenant;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\BuildsMenus;
use Tests\TestCase;

class ApiRestaurationClientTest extends TestCase
{
    use BuildsMenus;

    private const API_URL = 'https://api.example.test/menus';

    private Tenant $tenant;

    private DishCategory $plats;

    private DishCategory $garnitures;

    private string $day;

    protected function setUp(): void
    {
        parent::setUp();

        $this->day = now()->addDay()->format('Y-m-d');

        $this->tenant = Tenant::factory()->create([
            'name' => 'Lille',
            'meta' => [
                'api_type' => 'api-restauration',
                'api_url' => self::API_URL,
                'api_category_mapping' => ['Grill' => 'pole-chaud'],
            ],
        ]);

        [$parent] = $this->createCategoryTree($this->tenant, 'mains', 'Pole Chaud', 'Plats');
        $this->plats = DishCategory::where('tenant_id', $this->tenant->id)->where('name_slug', 'plats')->first();
        $this->garnitures = DishCategory::factory()->childOf($parent)->create([
            'name' => 'Garnitures',
            'type' => 'mains',
        ]);
    }

    /**
     * Build one raw row as returned by the "api-restauration" endpoint.
     *
     * @return array<string, string>
     */
    private function apiItem(array $overrides = []): array
    {
        return array_merge([
            'periode' => 'midi',
            'rupture' => 'FALSE',
            'feuille' => 'Grill',
            'date' => now()->addDay()->format('d/m/Y'),
            'dateUs' => now()->addDay()->format('Ymd'),
            'nom' => 'Poulet rôti',
            'info1' => '',
            'info2' => '',
            'accompagnement' => 'FALSE',
            'vegetarien' => 'FALSE',
            'bio' => 'FALSE',
            'local' => 'FALSE',
            'saison' => 'FALSE',
            'equitable' => 'FALSE',
            'peche' => 'FALSE',
            'france' => 'FALSE',
        ], $overrides);
    }

    private function client(): ApiRestaurationClient
    {
        return new ApiRestaurationClient($this->tenant->fresh());
    }

    private function fakeApi(array $items): void
    {
        Http::fake([self::API_URL => Http::response($items)]);
    }

    public function test_it_refuses_a_tenant_without_an_api_url(): void
    {
        $tenant = Tenant::factory()->create(['meta' => ['api_url' => null, 'api_type' => 'api-restauration']]);

        $this->expectExceptionMessage('has no API URL');
        new ApiRestaurationClient($tenant);
    }

    public function test_it_refuses_a_tenant_without_an_api_type(): void
    {
        $tenant = Tenant::factory()->create(['meta' => ['api_url' => self::API_URL, 'api_type' => null]]);

        $this->expectExceptionMessage('has no API Type');
        new ApiRestaurationClient($tenant);
    }

    public function test_it_refuses_an_unsupported_api_type(): void
    {
        $tenant = Tenant::factory()->create(['meta' => ['api_url' => self::API_URL, 'api_type' => 'autre']]);

        $this->expectExceptionMessage('has an invalid API Type');
        new ApiRestaurationClient($tenant);
    }

    public function test_it_maps_the_api_response_into_a_menu_per_date(): void
    {
        $this->fakeApi([$this->apiItem()]);

        $menus = $this->client()->getMenus();

        $this->assertArrayHasKey($this->day, $menus);
        $this->assertSame('Poulet rôti', $menus[$this->day]['dishes']['mains']['pole-chaud']['plats'][0]['name']);
    }

    public function test_it_joins_the_name_and_the_extra_information(): void
    {
        $this->fakeApi([$this->apiItem(['nom' => 'Poulet', 'info1' => 'rôti', 'info2' => 'et frites'])]);

        $menus = $this->client()->getMenus();

        $this->assertSame('Poulet rôti et frites', $menus[$this->day]['dishes']['mains']['pole-chaud']['plats'][0]['name']);
    }

    public function test_it_maps_the_nutritional_flags_to_tags(): void
    {
        $this->fakeApi([$this->apiItem([
            'vegetarien' => 'TRUE',
            'bio' => 'TRUE',
            'local' => 'TRUE',
            'saison' => 'TRUE',
            'equitable' => 'TRUE',
            'peche' => 'TRUE',
            'france' => 'TRUE',
        ])]);

        $menus = $this->client()->getMenus();

        $this->assertSame(
            ['vegetarian', 'organic', 'regional', 'seasonal', 'equitable_trade', 'sustainable_fishing', 'france'],
            $menus[$this->day]['dishes']['mains']['pole-chaud']['plats'][0]['tags']
        );
    }

    public function test_it_detects_halal_from_the_dish_name(): void
    {
        $this->fakeApi([$this->apiItem(['nom' => 'Kebab', 'info1' => 'Hallal'])]);

        $menus = $this->client()->getMenus();

        $this->assertContains('halal', $menus[$this->day]['dishes']['mains']['pole-chaud']['plats'][0]['tags']);
    }

    public function test_it_routes_accompaniments_to_the_garnitures_sub_category(): void
    {
        $this->fakeApi([$this->apiItem(['nom' => 'Haricots verts', 'accompagnement' => 'TRUE'])]);

        $menus = $this->client()->getMenus();

        $this->assertSame('Haricots verts', $menus[$this->day]['dishes']['mains']['pole-chaud']['garnitures'][0]['name']);
        $this->assertEmpty($menus[$this->day]['dishes']['mains']['pole-chaud']['plats']);
    }

    public function test_it_skips_items_that_are_not_served_at_lunch(): void
    {
        $this->fakeApi([
            $this->apiItem(),
            $this->apiItem(['nom' => 'Soupe du soir', 'periode' => 'soir']),
        ]);

        $menus = $this->client()->getMenus();

        $this->assertCount(1, $menus[$this->day]['dishes']['mains']['pole-chaud']['plats']);
    }

    public function test_it_skips_items_that_are_out_of_stock(): void
    {
        $this->fakeApi([
            $this->apiItem(),
            $this->apiItem(['nom' => 'Plat épuisé', 'rupture' => 'TRUE']),
        ]);

        $menus = $this->client()->getMenus();

        $this->assertCount(1, $menus[$this->day]['dishes']['mains']['pole-chaud']['plats']);
    }

    public function test_it_skips_a_category_that_is_not_mapped(): void
    {
        $this->fakeApi([
            $this->apiItem(),
            $this->apiItem(['nom' => 'Plat non mappé', 'feuille' => 'Inconnue']),
        ]);

        $menus = $this->client()->getMenus();

        $this->assertCount(1, $menus[$this->day]['dishes']['mains']['pole-chaud']['plats']);
    }

    public function test_it_ignores_dates_in_the_past(): void
    {
        $this->fakeApi([
            $this->apiItem(),
            $this->apiItem([
                'nom' => 'Plat d\'hier',
                'dateUs' => now()->subDay()->format('Ymd'),
            ]),
        ]);

        $menus = $this->client()->getMenus();

        $this->assertSame([$this->day], array_keys($menus));
    }

    public function test_a_recurring_item_is_added_to_every_date(): void
    {
        $tomorrow = now()->addDay()->format('Y-m-d');
        $dayAfter = now()->addDays(2)->format('Y-m-d');

        $this->fakeApi([
            $this->apiItem(),
            $this->apiItem(['nom' => 'Plat du surlendemain', 'dateUs' => now()->addDays(2)->format('Ymd')]),
            $this->apiItem(['nom' => 'Pâtes tous les jours', 'date' => 'TRUE']),
        ]);

        $menus = $this->client()->getMenus();

        foreach ([$tomorrow, $dayAfter] as $date) {
            $names = array_column($menus[$date]['dishes']['mains']['pole-chaud']['plats'], 'name');
            $this->assertContains('Pâtes tous les jours', $names);
        }
    }

    /**
     * Declare a second sheet whose "nom" acts as a title, as the Sandwichs pole does.
     */
    private function mapSandwichAsTitle(): void
    {
        $this->tenant->update([
            'meta' => array_merge($this->tenant->meta, [
                'api_category_mapping' => ['Grill' => 'pole-chaud', 'Sandwich' => 'pole-sandwich'],
                'api_category_name_is_title_mapping' => ['Sandwich' => true],
            ]),
        ]);
    }

    public function test_it_prefixes_the_name_as_a_title_whatever_the_item_order(): void
    {
        $this->mapSandwichAsTitle();
        $this->fakeApi([
            $this->apiItem(['feuille' => 'Sandwich', 'nom' => 'Le fermier', 'info1' => 'Pain ciabatta, bacon']),
            $this->apiItem(['nom' => 'Poulet rôti']),
        ]);

        $menus = $this->client()->getMenus();

        $this->assertSame(
            'Le fermier : Pain ciabatta, bacon',
            $menus[$this->day]['dishes']['mains']['pole-sandwich']['plats'][0]['name']
        );
    }

    public function test_the_title_prefix_does_not_leak_to_the_other_categories(): void
    {
        $this->mapSandwichAsTitle();
        $this->fakeApi([
            $this->apiItem(['nom' => 'Poulet rôti']),
            $this->apiItem(['feuille' => 'Sandwich', 'nom' => 'Le fermier', 'info1' => 'Pain ciabatta, bacon']),
        ]);

        $menus = $this->client()->getMenus();

        $this->assertSame('Poulet rôti', $menus[$this->day]['dishes']['mains']['pole-chaud']['plats'][0]['name']);
    }

    public function test_it_throws_when_the_api_answers_with_an_error(): void
    {
        Http::fake([self::API_URL => Http::response('boom', 500)]);

        $this->expectExceptionMessage('Erreur lors de la récupération du menu');
        $this->client()->getMenus();
    }

    public function test_it_forwards_its_logs_to_the_callback(): void
    {
        $this->fakeApi([$this->apiItem()]);
        $messages = [];

        $client = new ApiRestaurationClient($this->tenant->fresh(), function ($message) use (&$messages) {
            $messages[] = $message;
        });
        $client->getMenus();

        $this->assertNotEmpty($messages);
        $this->assertContains('Traitement de la date : '.$this->day, $messages);
    }

    public function test_update_menus_persists_the_dishes(): void
    {
        Event::fake([MenuUpdatedEvent::class]);

        $this->client()->updateMenus([
            $this->day => [
                'dishes' => [
                    'mains' => [
                        'pole-chaud' => [
                            'plats' => [['name' => 'Poulet rôti', 'tags' => ['halal']]],
                            'garnitures' => [['name' => 'Haricots verts', 'tags' => []]],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertDatabaseHas('dishes', [
            'tenant_id' => $this->tenant->id,
            'date' => $this->day,
            'name' => 'Poulet rôti',
            'dishes_category_id' => $this->plats->id,
        ]);
        $this->assertDatabaseHas('dishes', [
            'name' => 'Haricots verts',
            'dishes_category_id' => $this->garnitures->id,
        ]);
        Event::assertDispatched(MenuUpdatedEvent::class);
    }

    public function test_update_menus_removes_dishes_that_disappeared(): void
    {
        Event::fake([MenuUpdatedEvent::class]);
        $this->createDish($this->plats, $this->day, 'Ancien plat');

        $this->client()->updateMenus([
            $this->day => [
                'dishes' => ['mains' => ['pole-chaud' => ['plats' => [['name' => 'Nouveau plat', 'tags' => []]]]]],
            ],
        ]);

        $this->assertDatabaseMissing('dishes', ['name' => 'Ancien plat']);
        $this->assertDatabaseHas('dishes', ['name' => 'Nouveau plat']);
    }

    public function test_update_menus_keeps_the_existing_rows_when_replayed(): void
    {
        Event::fake([MenuUpdatedEvent::class]);
        $menus = [
            $this->day => [
                'dishes' => ['mains' => ['pole-chaud' => ['plats' => [['name' => 'Poulet rôti', 'tags' => ['halal']]]]]],
            ],
        ];

        $this->client()->updateMenus($menus);
        $id = Dish::where('name', 'Poulet rôti')->first()->id;
        $this->client()->updateMenus($menus);

        $this->assertSame($id, Dish::where('name', 'Poulet rôti')->first()->id);
    }

    public function test_update_menus_rejects_an_invalid_date(): void
    {
        $this->expectExceptionMessage('Format de date invalide');

        $this->client()->updateMenus([
            '02-03-2026' => ['dishes' => ['mains' => ['pole-chaud' => ['plats' => [['name' => 'Poulet', 'tags' => []]]]]]],
        ]);
    }

    public function test_update_menus_rejects_an_unknown_category(): void
    {
        $this->expectExceptionMessage('Catégories invalides');

        $this->client()->updateMenus([
            $this->day => ['dishes' => ['mains' => ['pole-inconnu' => ['plats' => [['name' => 'Poulet', 'tags' => []]]]]]],
        ]);
    }

    public function test_update_menus_rejects_an_unknown_tag(): void
    {
        $this->expectExceptionMessage('Tags invalides');

        $this->client()->updateMenus([
            $this->day => ['dishes' => ['mains' => ['pole-chaud' => ['plats' => [['name' => 'Poulet', 'tags' => ['delicieux']]]]]]],
        ]);
    }

    public function test_update_menus_refuses_an_empty_payload(): void
    {
        $this->expectExceptionMessage('No menus provided');

        $this->client()->updateMenus([]);
    }

    public function test_compare_menus_reports_nothing_when_the_database_already_matches(): void
    {
        $this->createDish($this->plats, $this->day, 'Poulet rôti', ['halal']);

        $diff = $this->client()->compareMenus([
            $this->day => [
                'dishes' => ['mains' => ['pole-chaud' => ['plats' => [['name' => 'Poulet rôti', 'tags' => ['halal']]]]]],
            ],
        ]);

        $this->assertSame([], $diff);
    }

    public function test_compare_menus_flags_a_dish_only_present_in_the_api(): void
    {
        $diff = $this->client()->compareMenus([
            $this->day => [
                'dishes' => ['mains' => ['pole-chaud' => ['plats' => [['name' => 'Poulet rôti', 'tags' => []]]]]],
            ],
        ]);

        $this->assertArrayHasKey($this->day, $diff);
        $this->assertSame('API', $diff[$this->day]['dishes']['mains']['pole-chaud']['plats'][0]['_inconsistency_from']);
    }

    public function test_compare_menus_flags_a_dish_only_present_in_the_database(): void
    {
        $this->createDish($this->plats, $this->day, 'Poulet rôti');
        $this->createDish($this->plats, $this->day, 'Plat en trop');

        $diff = $this->client()->compareMenus([
            $this->day => [
                'dishes' => ['mains' => ['pole-chaud' => ['plats' => [['name' => 'Poulet rôti', 'tags' => []]]]]],
            ],
        ]);

        $this->assertArrayHasKey($this->day, $diff);
    }

    public function test_compare_menus_flags_a_tag_difference(): void
    {
        $this->createDish($this->plats, $this->day, 'Poulet rôti', ['halal']);

        $diff = $this->client()->compareMenus([
            $this->day => [
                'dishes' => ['mains' => ['pole-chaud' => ['plats' => [['name' => 'Poulet rôti', 'tags' => ['vegetarian']]]]]],
            ],
        ]);

        $this->assertArrayHasKey($this->day, $diff);
    }

    public function test_compare_menus_refuses_an_empty_payload(): void
    {
        $this->expectExceptionMessage('No menus provided');

        $this->client()->compareMenus([]);
    }

    /**
     * The API repeats a dish row per allergen sheet, so the same dish can show
     * up twice for a date. Keeping both would leave a diff nothing can resolve.
     */
    public function test_a_dish_repeated_by_the_api_is_kept_once(): void
    {
        Event::fake([MenuUpdatedEvent::class]);
        $this->fakeApi([
            $this->apiItem(['nom' => 'Poulet rôti', 'bio' => 'TRUE']),
            $this->apiItem(['nom' => 'Poulet rôti', 'bio' => 'TRUE']),
        ]);

        $client = $this->client();
        $menus = $client->getMenus();

        $this->assertCount(1, $menus[$this->day]['dishes']['mains']['pole-chaud']['plats']);

        $client->updateMenus($menus);

        $this->assertSame(1, Dish::where('name', 'Poulet rôti')->count());
        $this->assertSame([], $client->compareMenus($menus));
    }

    public function test_the_api_response_can_be_replayed_end_to_end(): void
    {
        Event::fake([MenuUpdatedEvent::class]);
        $this->fakeApi([
            $this->apiItem(['nom' => 'Poulet rôti', 'bio' => 'TRUE']),
            $this->apiItem(['nom' => 'Haricots verts', 'accompagnement' => 'TRUE']),
        ]);

        $client = $this->client();
        $menus = $client->getMenus();

        $this->assertNotEmpty($client->compareMenus($menus));

        $client->updateMenus($menus);

        $this->assertSame([], $client->compareMenus($menus));
        $this->assertSame(['organic'], Dish::where('name', 'Poulet rôti')->first()->tags);
    }
}
