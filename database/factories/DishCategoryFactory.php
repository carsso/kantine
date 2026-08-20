<?php

namespace Database\Factories;

use App\Models\DishCategory;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DishCategory>
 */
class DishCategoryFactory extends Factory
{
    protected $model = DishCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->unique()->word(),
            'type' => 'mains',
            'hidden' => false,
            'hidden_from_dashboard' => false,
            'color' => '#000000',
            'icon' => 'fa-utensils',
            'emoji' => '🍽️',
            'parent_id' => null,
            'sort_order' => 0,
            'meta' => [],
        ];
    }

    /**
     * Attach the category to a parent category, making it a sub-category.
     */
    public function childOf(DishCategory $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'tenant_id' => $parent->tenant_id,
        ]);
    }

    /**
     * Exclude the category's dishes from the special day detection (fries, burgers...).
     */
    public function ignoringSpecialDay(): static
    {
        return $this->state(fn (array $attributes) => [
            'meta' => array_merge($attributes['meta'] ?? [], ['ignore_special_day' => true]),
        ]);
    }

    /**
     * Give the category an external link, used by the category link redirect.
     */
    public function withLink(string $url): static
    {
        return $this->state(fn (array $attributes) => [
            'meta' => array_merge($attributes['meta'] ?? [], ['link_url' => $url]),
        ]);
    }
}
