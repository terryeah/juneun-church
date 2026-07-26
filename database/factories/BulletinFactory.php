<?php

namespace Database\Factories;

use App\Models\Bulletin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory for bulletin test records.
 *
 * @extends Factory<Bulletin>
 */
class BulletinFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => '주보 '.fake()->date('Y-m-d'),
            'file_path' => 'bulletins/'.Str::uuid().'.pdf',
            'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
