<?php

namespace Database\Factories;

use App\Models\Position;
use App\Models\StaffMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for staff member test records.
 *
 * @extends Factory<StaffMember>
 */
class StaffMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'position_id' => Position::factory(),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_published' => true,
        ];
    }
}
