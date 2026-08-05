<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the four staff roles the church actually fills. The
 * permissionless 'member' role that approved 가입 신청 receive is
 * created by its own migration rather than here.
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
        ])->each(fn (string $name) => Role::findOrCreate($name, 'web'));
    }
}
