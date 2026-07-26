<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeds the application database.
 *
 * Reference data (roles, positions, service types, site settings) and the
 * initial super_admin account are always seeded. Demonstration content is
 * seeded only in the local environment.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            RolePermissionSeeder::class,
            PositionSeeder::class,
            ServiceTypeSeeder::class,
            SiteSettingSeeder::class,
            SuperAdminSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->call(DemoContentSeeder::class);
        }
    }
}
