<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Adds the 재정 담당 role that 재정부 signs in with.
 *
 * The department counts the offerings and keeps the church's books, so
 * the role holds every permission of 헌금 내역 and 개인 헌금 and
 * nothing else: no 성도, no 셀, no 사용자, no 가입 신청, no 사이트
 * 설정 and no 직분, because none of those are needed to write down what
 * was given and all of them carry the congregation's personal details.
 *
 * RolePermissionSeeder carries the same grant, but seeders are not
 * re-run against production, so the role is created and granted here as
 * well.
 */
return new class extends Migration
{
    /**
     * The role being added.
     */
    protected string $role = 'finance_officer';

    /**
     * Create the role and grant it the offering permissions.
     */
    public function up(): void
    {
        DB::table('roles')->insertOrIgnore([
            'name' => $this->role,
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $role = DB::table('roles')->where('name', $this->role)->value('id');

        foreach ($this->permissionIds() as $permission) {
            DB::table('role_has_permissions')->insertOrIgnore([
                'permission_id' => $permission,
                'role_id' => $role,
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Drop the role again, unless a real person is still signing in with
     * it. Deleting a role cascades to model_has_roles, and
     * User::canAccessPanel() admits an account on the strength of holding
     * any role at all, so a finance officer left role-less would simply
     * stop being able to sign in with nothing on screen explaining why.
     */
    public function down(): void
    {
        if (! $role = DB::table('roles')->where('name', $this->role)->value('id')) {
            return;
        }

        $holders = DB::table('model_has_roles')
            ->join('users', 'users.id', '=', 'model_has_roles.model_id')
            ->where('model_has_roles.model_type', User::class)
            ->where('model_has_roles.role_id', $role)
            ->where('users.is_test_account', false)
            ->pluck('users.email');

        if ($holders->isNotEmpty()) {
            throw new RuntimeException(
                "Cannot drop the {$this->role} role: still held by ".$holders->implode(', ')
                .'. Give each of them another role first, because an account with no role '
                .'cannot sign in to the admin panel at all.',
            );
        }

        DB::table('roles')->where('id', $role)->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Every permission covering the two offering models, matched on the
     * model suffix the way RolePermissionSeeder does. The colon keeps
     * ':Offering' from swallowing ':PersonalOffering'.
     *
     * @return array<int, int>
     */
    protected function permissionIds(): array
    {
        return DB::table('permissions')
            ->where('name', 'like', '%:Offering')
            ->orWhere('name', 'like', '%:PersonalOffering')
            ->pluck('id')
            ->all();
    }
};
