<?php

namespace Database\Factories;

use App\Models\Information;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Information>
 */
class InformationFactory extends Factory
{
    protected $model = Information::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'date' => now()->format('Y-m-d'),
            'event_name' => null,
            'information' => null,
            'style' => null,
        ];
    }

    /**
     * Announce an event on that day.
     */
    public function event(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'event_name' => $name,
        ]);
    }
}
