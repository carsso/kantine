<?php

namespace Tests\Feature;

use App\Models\DishCategory;
use App\Models\Information;
use App\Models\Tenant;
use App\Services\DayService;
use Tests\Concerns\BuildsMenus;
use Tests\TestCase;

class DayServiceTest extends TestCase
{
    use BuildsMenus;

    private DayService $dayService;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dayService = app(DayService::class);
        $this->tenant = Tenant::factory()->create();
    }

    public function test_it_defaults_to_today_when_no_date_is_given(): void
    {
        $day = $this->dayService->getDay($this->tenant);

        $this->assertSame(now()->format('Y-m-d'), $day['date']);
    }

    public function test_it_falls_back_to_today_for_a_malformed_date(): void
    {
        $day = $this->dayService->getDay($this->tenant, 'not-a-date');

        $this->assertSame(now()->format('Y-m-d'), $day['date']);
    }

    public function test_it_only_returns_dishes_of_the_requested_day(): void
    {
        [, $child] = $this->createCategoryTree($this->tenant);
        $this->createDish($child, '2026-03-02', 'Poulet rôti');
        $this->createDish($child, '2026-03-03', 'Boeuf bourguignon');

        $day = $this->dayService->getDay($this->tenant, '2026-03-02');

        $dishes = $day['dishes']['mains']['pole-chaud']['plats'];
        $this->assertCount(1, $dishes);
        $this->assertSame('Poulet rôti', $dishes->first()->name);
    }

    public function test_it_does_not_leak_dishes_from_another_tenant(): void
    {
        $otherTenant = Tenant::factory()->create();
        [, $ownCategory] = $this->createCategoryTree($this->tenant);
        [, $otherCategory] = $this->createCategoryTree($otherTenant);

        $this->createDish($ownCategory, '2026-03-02', 'Poulet rôti');
        $this->createDish($otherCategory, '2026-03-02', 'Plat du voisin');

        $day = $this->dayService->getDay($this->tenant, '2026-03-02');

        $this->assertCount(1, $day['dishes']['mains']['pole-chaud']['plats']);
    }

    public function test_it_groups_dishes_by_type_then_root_category_then_sub_category(): void
    {
        [$parent] = $this->createCategoryTree($this->tenant, 'mains', 'Pole Chaud', 'Plats');
        $garnitures = DishCategory::factory()->childOf($parent)->create([
            'name' => 'Garnitures',
            'type' => 'mains',
            'sort_order' => 1,
        ]);
        [, $dessert] = $this->createCategoryTree($this->tenant, 'desserts', 'Pole Froid', 'Desserts');

        $child = DishCategory::where('name_slug', 'plats')->first();
        $this->createDish($child, '2026-03-02', 'Poulet rôti');
        $this->createDish($garnitures, '2026-03-02', 'Frites');
        $this->createDish($dessert, '2026-03-02', 'Tarte');

        $day = $this->dayService->getDay($this->tenant, '2026-03-02');

        $this->assertEqualsCanonicalizing(['mains', 'desserts'], $day['dishes']->keys()->all());
        $this->assertEqualsCanonicalizing(
            ['plats', 'garnitures'],
            $day['dishes']['mains']['pole-chaud']->keys()->all()
        );
        $this->assertCount(1, $day['dishes']['desserts']['pole-froid']['desserts']);
    }

    public function test_it_sorts_root_categories_by_sort_order(): void
    {
        $this->createCategoryTree($this->tenant, 'mains', 'Second Pole', 'Plats B', 2);
        $this->createCategoryTree($this->tenant, 'mains', 'First Pole', 'Plats A', 1);

        $this->createDish(DishCategory::where('name_slug', 'plats-b')->first(), '2026-03-02', 'B');
        $this->createDish(DishCategory::where('name_slug', 'plats-a')->first(), '2026-03-02', 'A');

        $day = $this->dayService->getDay($this->tenant, '2026-03-02');

        $this->assertSame(['first-pole', 'second-pole'], $day['dishes']['mains']->keys()->all());
    }

    public function test_it_flags_fries_burgers_and_antioxidants_days(): void
    {
        [, $child] = $this->createCategoryTree($this->tenant);
        $this->createDish($child, '2026-03-02', 'Steak et Frites maison');
        $this->createDish($child, '2026-03-02', 'Burger végétarien');
        $this->createDish($child, '2026-03-02', 'Salade de Lentilles');

        $day = $this->dayService->getDay($this->tenant, '2026-03-02');

        $this->assertTrue($day['is_fries_day']);
        $this->assertTrue($day['is_burgers_day']);
        $this->assertTrue($day['is_antioxidants_day']);
    }

    public function test_it_does_not_flag_a_plain_day(): void
    {
        [, $child] = $this->createCategoryTree($this->tenant);
        $this->createDish($child, '2026-03-02', 'Poulet rôti');

        $day = $this->dayService->getDay($this->tenant, '2026-03-02');

        $this->assertFalse($day['is_fries_day']);
        $this->assertFalse($day['is_burgers_day']);
        $this->assertFalse($day['is_antioxidants_day']);
    }

    public function test_categories_marked_ignore_special_day_do_not_trigger_the_flags(): void
    {
        [$parent] = $this->createCategoryTree($this->tenant);
        $sideBar = DishCategory::factory()
            ->childOf($parent)
            ->ignoringSpecialDay()
            ->create(['name' => 'Snacks', 'type' => 'mains']);

        $this->createDish($sideBar, '2026-03-02', 'Frites en libre service');

        $day = $this->dayService->getDay($this->tenant, '2026-03-02');

        $this->assertFalse($day['is_fries_day']);
    }

    public function test_it_finds_the_next_fries_day_after_the_requested_date(): void
    {
        [, $child] = $this->createCategoryTree($this->tenant);
        $this->createDish($child, '2026-03-02', 'Frites du jour');
        $this->createDish($child, '2026-03-05', 'Poisson et Frites');
        $this->createDish($child, '2026-03-09', 'Frites plus tard');

        $day = $this->dayService->getDay($this->tenant, '2026-03-02');

        $this->assertNotNull($day['next_fries_day']);
        $this->assertSame('2026-03-05', $day['next_fries_day']->date);
    }

    public function test_next_fries_day_ignores_categories_marked_ignore_special_day(): void
    {
        [$parent, $child] = $this->createCategoryTree($this->tenant);
        $ignored = DishCategory::factory()
            ->childOf($parent)
            ->ignoringSpecialDay()
            ->create(['name' => 'Snacks', 'type' => 'mains']);

        $this->createDish($ignored, '2026-03-05', 'Frites en libre service');
        $this->createDish($child, '2026-03-06', 'Moules Frites');

        $day = $this->dayService->getDay($this->tenant, '2026-03-02');

        $this->assertSame('2026-03-06', $day['next_fries_day']->date);
    }

    public function test_next_burgers_and_antioxidants_days_are_resolved(): void
    {
        [, $child] = $this->createCategoryTree($this->tenant);
        $this->createDish($child, '2026-03-04', 'Burger du chef');
        $this->createDish($child, '2026-03-06', 'Chili aux Haricots rouges');

        $day = $this->dayService->getDay($this->tenant, '2026-03-02');

        $this->assertSame('2026-03-04', $day['next_burgers_day']->date);
        $this->assertSame('2026-03-06', $day['next_antioxidants_day']->date);
    }

    public function test_it_returns_null_when_there_is_no_upcoming_special_day(): void
    {
        [, $child] = $this->createCategoryTree($this->tenant);
        $this->createDish($child, '2026-03-04', 'Poulet rôti');

        $day = $this->dayService->getDay($this->tenant, '2026-03-02');

        $this->assertNull($day['next_fries_day']);
        $this->assertNull($day['next_burgers_day']);
        $this->assertNull($day['next_antioxidants_day']);
    }

    public function test_it_returns_the_information_of_the_day_when_it_has_content(): void
    {
        Information::factory()->create([
            'tenant_id' => $this->tenant->id,
            'date' => '2026-03-02',
            'event_name' => 'Semaine du goût',
        ]);

        $day = $this->dayService->getDay($this->tenant, '2026-03-02');

        $this->assertNotNull($day['information']);
        $this->assertSame('Semaine du goût', $day['information']->event_name);
    }

    public function test_it_returns_null_information_when_the_row_is_empty(): void
    {
        Information::factory()->create([
            'tenant_id' => $this->tenant->id,
            'date' => '2026-03-02',
        ]);

        $day = $this->dayService->getDay($this->tenant, '2026-03-02');

        $this->assertNull($day['information']);
    }

    public function test_it_finds_the_next_event(): void
    {
        Information::factory()->event('Chandeleur')->create([
            'tenant_id' => $this->tenant->id,
            'date' => '2026-03-05',
        ]);
        Information::factory()->create([
            'tenant_id' => $this->tenant->id,
            'date' => '2026-03-04',
            'information' => 'Pas un événement',
        ]);

        $day = $this->dayService->getDay($this->tenant, '2026-03-02');

        $this->assertNotNull($day['next_event']);
        $this->assertSame('Chandeleur', $day['next_event']->event_name);
    }
}
