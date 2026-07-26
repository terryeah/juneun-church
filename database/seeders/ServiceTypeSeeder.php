<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

/**
 * Seeds the worship service types.
 */
class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => '주일예배', 'sort_order' => 10],
            ['name' => '수요예배', 'sort_order' => 20],
            ['name' => '금요기도회', 'sort_order' => 30],
            ['name' => '주일학교', 'sort_order' => 40],
            ['name' => '특별예배', 'sort_order' => 50],
        ];

        foreach ($types as $type) {
            ServiceType::query()->firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
