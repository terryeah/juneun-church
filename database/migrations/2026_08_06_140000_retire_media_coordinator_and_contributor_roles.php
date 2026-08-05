<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * Retires the media_coordinator and contributor roles. Both were tried
 * and neither earned its keep: content_editor is the one non-admin,
 * non-developer role the church actually staffs, and every role that
 * exists is another permission matrix to keep honest.
 *
 * Deleting a role cascades to role_has_permissions and
 * model_has_roles, so anyone still holding one would silently lose it
 * - and User::canAccessPanel() lets an account in on the strength of
 * holding any role at all, so a person left role-less cannot sign in
 * and has nothing on screen explaining why. That failure surfaces days
 * later as "the site is broken". This migration therefore refuses to
 * run while a real person holds either role: the operator moves them
 * to content_editor (or admin) first and migrates afterwards. Test
 * accounts are ignored because the migration before this one deletes
 * them outright.
 */
return new class extends Migration
{
    /**
     * The roles being retired, mapped to the permissions they held, so
     * down() can put them back as they were.
     *
     * @var array<string, list<string>>
     */
    protected array $retired = [
        'media_coordinator' => ['%:Album', '%:Photo'],
        'contributor' => ['ViewAny:Album', 'View:Album', 'ViewAny:Photo', 'View:Photo', 'Create:Photo'],
    ];

    /**
     * Drop both roles once nobody real is left holding them.
     */
    public function up(): void
    {
        $roles = DB::table('roles')->whereIn('name', array_keys($this->retired))->pluck('id', 'name');

        if ($roles->isEmpty()) {
            return;
        }

        $holders = DB::table('model_has_roles')
            ->join('users', 'users.id', '=', 'model_has_roles.model_id')
            ->where('model_has_roles.model_type', User::class)
            ->whereIn('model_has_roles.role_id', $roles->values())
            ->where('users.is_test_account', false)
            ->pluck('users.email');

        if ($holders->isNotEmpty()) {
            throw new RuntimeException(
                'Cannot retire '.implode(' and ', array_keys($this->retired)).': still held by '
                .$holders->implode(', ').'. Give each of them another role first, '
                .'because an account with no role cannot sign in to the admin panel at all.',
            );
        }

        DB::table('roles')->whereIn('id', $roles->values())->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Recreate both roles with the permissions they used to hold. The
     * users who held them are not restored: their assignments went with
     * the role, and only this migration's own deletion is reversible.
     */
    public function down(): void
    {
        $now = now();

        foreach ($this->retired as $name => $patterns) {
            DB::table('roles')->insertOrIgnore([
                'name' => $name,
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $role = DB::table('roles')->where('name', $name)->value('id');

            $permissions = DB::table('permissions')
                ->where(function ($query) use ($patterns): void {
                    foreach ($patterns as $pattern) {
                        $query->orWhere('name', 'like', $pattern);
                    }
                })
                ->pluck('id');

            foreach ($permissions as $permission) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'permission_id' => $permission,
                    'role_id' => $role,
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
