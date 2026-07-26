<?php

namespace Database\Factories;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory for announcement test records.
 *
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'content' => '<p>'.fake()->paragraph().'</p>',
            'is_published' => true,
            'is_pinned' => false,
            'published_at' => now()->subDays(fake()->numberBetween(0, 30)),
        ];
    }

    /**
     * Mark the announcement as pinned.
     */
    public function pinned(): static
    {
        return $this->state(['is_pinned' => true]);
    }

    /**
     * Mark the announcement as unpublished.
     */
    public function draft(): static
    {
        return $this->state(['is_published' => false]);
    }
}
