<?php

namespace Database\Factories;

use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for position test records.
 *
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['담임목사', '부목사', '전도사', '장로', '권사', '집사']),
            'category' => fake()->randomElement(['pastoral', 'elder', 'deacon', 'volunteer']),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
