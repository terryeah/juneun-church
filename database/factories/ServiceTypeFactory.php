<?php

namespace Database\Factories;

use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for service type test records.
 *
 * @extends Factory<ServiceType>
 */
class ServiceTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['주일예배', '수요예배', '금요기도회', '주일학교', '특별예배']),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
