<?php

namespace Database\Factories;

use App\Models\Dish;
use App\Models\DishCategory;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dish>
 */
class DishFactory extends Factory
{
    protected $model = Dish::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'dishes_category_id' => DishCategory::factory(),
            'date' => now()->format('Y-m-d'),
            'name' => fake()->words(3, true),
            'tags' => [],
        ];
    }

    /**
     * Place the dish in a category, inheriting its tenant.
     */
    public function inCategory(DishCategory $category): static
    {
        return $this->state(fn (array $attributes) => [
            'dishes_category_id' => $category->id,
            'tenant_id' => $category->tenant_id,
        ]);
    }

    /**
     * Serve the dish on the given date.
     */
    public function on(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }
}
