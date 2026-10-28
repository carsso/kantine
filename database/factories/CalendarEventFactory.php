<?php

namespace Database\Factories;

use App\Models\CalendarEvent;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalendarEvent>
 */
class CalendarEventFactory extends Factory
{
    protected $model = CalendarEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'label' => 'Coupe du monde',
            'style' => 'world_cup',
            'starts_on' => '2026-06-11',
            'ends_on' => '2026-07-19',
        ];
    }
}
