<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Offering records are financial, so only administrators may reach
 * them. The offerings tables cloned their permissions from
 * announcements, which handed them to content editors as well - this
 * brings the grants back in line with RolePermissionSeeder.
 */
return new class extends Migration
{
    /**
     * Roles that keep their offering permissions.
     *
     * @var list<string>
     */
    protected array $keep = ['super_admin', 'admin', 'developer'];

    /**
     * Revoke offering permissions from every other role.
     */
    public function up(): void
    {
        DB::table('role_has_permissions')
            ->whereIn('permission_id', $this->offeringPermissionIds())
            ->whereIn('role_id', DB::table('roles')->whereNotIn('name', $this->keep)->pluck('id'))
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Restore the content editor's offering permissions.
     */
    public function down(): void
    {
        $editor = DB::table('roles')->where('name', 'content_editor')->value('id');

        if ($editor === null) {
            return;
        }

        foreach ($this->offeringPermissionIds() as $permission) {
            DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $permission,
                'role_id' => $editor,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Every permission covering the two offering models.
     *
     * @return array<int, int>
     */
    protected function offeringPermissionIds(): array
    {
        return DB::table('permissions')
            ->where('name', 'like', '%:Offering')
            ->orWhere('name', 'like', '%:PersonalOffering')
            ->pluck('id')
            ->all();
    }
};
