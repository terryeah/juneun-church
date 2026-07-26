<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for event test records.
 *
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'event_date' => fake()->dateTimeBetween('now', '+2 months'),
            'event_time' => fake()->time('H:i'),
            'location' => fake()->randomElement(['본당', '교육관', 'Rocks Riverside Park']),
            'is_published' => true,
        ];
    }
}
