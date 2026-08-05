<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Hands 사이트 설정, 예배 종류 and 부서 back to content editors.
 *
 * These three hold reference data - a phone number, the list of
 * services, the list of departments - and locking them to
 * administrators meant the secretary had to ask someone else to fix a
 * typo. The sidebar's admin badge on each of them is derived from
 * these grants, so it disappears with them.
 *
 * RolePermissionSeeder carries the same change, but seeders are not
 * re-run against production, so the grant is applied here as well.
 */
return new class extends Migration
{
    /**
     * The models whose full permission set content editors regain.
     *
     * @var list<string>
     */
    protected array $models = ['SiteSetting', 'ServiceType', 'Ministry'];

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
     * Every permission covering the three reference models, matched on
     * the model suffix the way RolePermissionSeeder does.
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
