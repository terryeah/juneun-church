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

        /** 셀 and 가입 신청 arrived with their own migrations, which granted them to admin there. */
        Role::findOrCreate('admin', 'web')->syncPermissions($forModels([
            'Announcement', 'Event', 'Position', 'Member', 'Cell', 'MembershipRequest',
            'Offering', 'PersonalOffering', 'ServiceType', 'Sermon', 'Album', 'Photo',
            'Bulletin', 'SiteSetting', 'User',
        ]));

        /**
         * Reference data - a phone number, the services, the departments - is
         * editing, not administration, and so are the gallery and the list of
         * position names. 성도 and 셀 are not: they hold the congregation's
         * personal details, so the whole 공동체 group stays with
         * administrators.
         */
        Role::findOrCreate('content_editor', 'web')->syncPermissions($forModels([
            'Announcement', 'Event', 'Sermon', 'Bulletin', 'SiteSetting', 'ServiceType', 'Ministry',
            'Album', 'Photo', 'Position',
        ]));
    }
}
