<?php

namespace Database\Factories;

use App\Models\Sermon;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for sermon test records.
 *
 * @extends Factory<Sermon>
 */
class SermonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'youtube_video_id' => fake()->regexify('[A-Za-z0-9_-]{11}'),
            'preacher' => '엄현준 담임목사',
            'sermon_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'service_type_id' => ServiceType::factory(),
            'scripture_reference' => '요한복음 3:16',
            'is_published' => true,
        ];
    }
}
