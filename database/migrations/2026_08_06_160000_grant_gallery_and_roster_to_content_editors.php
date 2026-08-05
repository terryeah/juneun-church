<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Opens 앨범, 사진 and 직분 to content editors.
 *
 * The owner asked for the administrator tag to come off these three in
 * the sidebar. That tag is derived from who holds the ViewAny
 * permission, so the tag only goes away by actually widening access.
 *
 * 성도 and 셀 were considered alongside them and deliberately left out:
 * a 성도 record carries a birth date, a phone number, an address and an
 * email address, and 셀 arranges those people into small groups, so the
 * whole 공동체 group stays with administrators. The three granted here
 * hold photographs and a list of position names, nothing personal.
 *
 * RolePermissionSeeder carries the same change, but seeders are not
 * re-run against production, so the grant is applied here as well.
 */
return new class extends Migration
{
    /**
     * The models whose full permission set content editors gain.
     *
     * @var list<string>
     */
    protected array $models = ['Album', 'Photo', 'Position'];

    /**
     * Grant every permission of those models to content_editor.
     */
    public function up(): void
    {
        if (! $role = DB::table('roles')->where('name', 'content_editor')->value('id')) {
            return;
        }

        foreach ($this->permissionIds() as $permission) {
            DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $permission,
                'role_id' => $role,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Put the three back behind the administrator badge.
     */
    public function down(): void
    {
        if (! $role = DB::table('roles')->where('name', 'content_editor')->value('id')) {
            return;
        }

        DB::table('role_has_permissions')
            ->where('role_id', $role)
            ->whereIn('permission_id', $this->permissionIds())
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Every permission covering the three models, matched on the model
     * suffix the way RolePermissionSeeder does.
     *
     * @return array<int, int>
     */
    protected function permissionIds(): array
    {
        return DB::table('permissions')
            ->where(function ($query): void {
                foreach ($this->models as $model) {
                    $query->orWhere('name', 'like', "%:{$model}");
                }
            })
            ->pluck('id')
            ->all();
    }
};
