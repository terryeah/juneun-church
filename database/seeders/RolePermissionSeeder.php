<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Assigns Shield-generated permissions to roles per the permission matrix.
 *
 * Permissions are matched by their model suffix (for example
 * "Update:Announcement") so the mapping survives changes to the set of
 * actions Shield generates. Run after shield:generate.
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $all = Permission::query()->pluck('name');

        $forModels = fn (array $models) => $all->filter(
            fn (string $name) => in_array(str($name)->afterLast(':')->value(), $models, true),
        )->all();

        Role::findOrCreate('super_admin', 'web')->syncPermissions($all->all());

        Role::findOrCreate('developer', 'web')->syncPermissions($all->all());

        Role::findOrCreate('admin', 'web')->syncPermissions($forModels([
            'Announcement', 'Event', 'Position', 'Member', 'Offering', 'ServiceType',
            'Sermon', 'Album', 'Photo', 'Bulletin', 'SiteSetting', 'User',
        ]));

        Role::findOrCreate('content_editor', 'web')->syncPermissions($forModels([
            'Announcement', 'Event', 'Sermon', 'Bulletin',
        ]));

        Role::findOrCreate('media_coordinator', 'web')->syncPermissions($forModels([
            'Album', 'Photo',
        ]));

        Role::findOrCreate('contributor', 'web')->syncPermissions(
            $all->filter(fn (string $name) => in_array($name, ['ViewAny:Photo', 'View:Photo', 'Create:Photo', 'ViewAny:Album', 'View:Album'], true))->all(),
        );
    }
}
