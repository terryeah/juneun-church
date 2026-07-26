<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

/**
 * Seeds the standard church positions in hierarchical order.
 */
class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            ['name' => '담임목사', 'category' => 'pastoral', 'sort_order' => 10],
            ['name' => '부목사', 'category' => 'pastoral', 'sort_order' => 20],
            ['name' => '전도사', 'category' => 'pastoral', 'sort_order' => 30],
            ['name' => '장로', 'category' => 'elder', 'sort_order' => 40],
            ['name' => '권사', 'category' => 'deacon', 'sort_order' => 50],
            ['name' => '집사', 'category' => 'deacon', 'sort_order' => 60],
            ['name' => '봉사자', 'category' => 'volunteer', 'sort_order' => 70],
        ];

        foreach ($positions as $position) {
            Position::query()->firstOrCreate(['name' => $position['name']], $position);
        }
    }
}
