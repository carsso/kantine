<?php

namespace Tests\Concerns;

use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\Tenant;

/**
 * Helpers to build the tenant / category / dish tree the menu code expects.
 *
 * Dishes are always resolved through their category *and* that category's
 * parent, so a dish without a two-level category tree blows up the menu code.
 */
trait BuildsMenus
{
    /**
     * Create a root category and one sub-category under it.
     *
     * @return array{0: DishCategory, 1: DishCategory}
     */
    protected function createCategoryTree(
        Tenant $tenant,
        string $type = 'mains',
        string $parentName = 'Pole Chaud',
        string $childName = 'Plats',
        int $sortOrder = 0
    ): array {
        $parent = DishCategory::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => $parentName,
            'type' => $type,
            'sort_order' => $sortOrder,
        ]);

        $child = DishCategory::factory()->childOf($parent)->create([
            'name' => $childName,
            'type' => $type,
            'sort_order' => $sortOrder,
        ]);

        return [$parent, $child];
    }

    /**
     * Create a dish served on $date inside $category.
     *
     * @param  array<int, string>  $tags
     */
    protected function createDish(DishCategory $category, string $date, string $name, array $tags = []): Dish
    {
        return Dish::factory()->inCategory($category)->on($date)->create([
            'name' => $name,
            'tags' => $tags,
        ]);
    }
}
