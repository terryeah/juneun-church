<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory for photo test records.
 *
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = Str::uuid().'.jpg';

        return [
            'album_id' => Album::factory(),
            'filename' => $filename,
            'original_filename' => fake()->word().'.jpg',
            'path' => 'gallery/'.$filename,
            'width' => 1200,
            'height' => 800,
            'file_size' => fake()->numberBetween(100_000, 900_000),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
