<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city(),
            'is_active' => true,
            'webex_bearer_token' => null,
            'webex_bot_name' => null,
            'meta' => [],
        ];
    }

    /**
     * Indicate that the tenant is not publicly visible.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the tenant pulls its menus from the "api-restauration" API.
     */
    public function withRestaurationApi(string $apiUrl = 'https://api.example.test/menus'): static
    {
        return $this->state(fn (array $attributes) => [
            'meta' => array_merge($attributes['meta'] ?? [], [
                'api_type' => 'api-restauration',
                'api_url' => $apiUrl,
            ]),
        ]);
    }

    /**
     * Indicate that the tenant can send Webex notifications.
     */
    public function withWebex(): static
    {
        return $this->state(fn (array $attributes) => [
            'webex_bearer_token' => 'webex-token',
            'webex_bot_name' => 'bot@webex.bot',
        ]);
    }
}
