<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory for videos held on the church's YouTube channel.
 *
 * @extends Factory<Video>
 */
class VideoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'album_id' => Album::factory()->state(['type' => Album::TYPE_VIDEO]),
            'youtube_id' => Str::random(11),
            'title' => fake()->sentence(3),
            'sort_order' => 0,
        ];
    }
}
