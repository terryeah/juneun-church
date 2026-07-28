<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the five application roles defined in the project specification.
 *
 * Role-to-permission assignments are managed with Filament Shield after
 * permissions have been generated for each resource.
 */
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        collect([
            'super_admin',
            'developer',
            'admin',
            'content_editor',
            'media_coordinator',
            'contributor',
        ])->each(fn (string $name) => Role::findOrCreate($name, 'web'));
    }
}
