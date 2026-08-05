<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Returns 사이트 설정 and 직분 to administrators.
 *
 * Site settings carry the service times, both addresses and the giving
 * account numbers; positions carry the church's order of office. A
 * mistake in either reaches the congregation rather than the website,
 * which is why large churches keep both away from content editors.
 */
return new class extends Migration
{
    /**
     * Models moving back behind the administrator line.
     *
     * @var list<string>
     */
    protected array $models = ['SiteSetting', 'Position'];

    /**
     * Revoke the content editor's grants.
     */
    public function up(): void
    {
        $editor = DB::table('roles')->where('name', 'content_editor')->value('id');

        if ($editor === null) {
            return;
        }

        DB::table('role_has_permissions')
            ->where('role_id', $editor)
            ->whereIn('permission_id', $this->permissionIds())
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Hand them back.
     */
    public function down(): void
    {
        $editor = DB::table('roles')->where('name', 'content_editor')->value('id');

        if ($editor === null) {
            return;
        }

        foreach ($this->permissionIds() as $permission) {
            DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $permission,
                'role_id' => $editor,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Every permission covering the two models.
     *
     * @return array<int, int>
     */
    protected function permissionIds(): array
    {
        return DB::table('permissions')
            ->where(function ($query): void {
                foreach ($this->models as $model) {
                    $query->orWhere('name', 'like', '%:'.$model);
                }
            })
            ->pluck('id')
            ->all();
    }
};
