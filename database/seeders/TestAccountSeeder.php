<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Creates one throwaway account per role so each role's view of the
 * site can be checked without touching a real person's login.
 *
 * Locally the password is the memorable 'test'. Anywhere else it is
 * generated per account and printed once, because these accounts sit
 * on a site holding the congregation's personal details and a
 * guessable staff password would hand that over to anyone who tries.
 */
class TestAccountSeeder extends Seeder
{
    /**
     * Create or refresh the per-role test accounts.
     */
    public function run(): void
    {
        /**
         * super_admin is skipped on purpose: a throwaway account with
         * unrestricted control is the one thing here worth attacking,
         * and the owner's own account already covers that view.
         */
        $roles = Role::query()->whereNot('name', 'super_admin')->pluck('name');
        $rows = [];

        foreach ($roles as $role) {
            $password = app()->environment('local') ? 'test' : Str::password(16);

            $user = User::query()->updateOrCreate(
                ['email' => "test-{$role}@juneun.com"],
                [
                    'name' => "테스트 {$role}",
                    'password' => Hash::make($password),
                ],
            );

            $user->syncRoles([$role]);

            /** Every login belongs to a roster record, so give each one an unpublished member. */
            Member::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => "테스트 {$role}",
                    'status' => '재적',
                    'position_id' => Position::query()->where('name', '성도')->value('id'),
                    'is_published' => false,
                ],
            );

            $rows[] = [$user->email, $role, $password];
        }

        $this->command?->table(['email', 'role', 'password'], $rows);
    }
}
