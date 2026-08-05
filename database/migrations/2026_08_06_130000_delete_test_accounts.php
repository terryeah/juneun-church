<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Removes the throwaway per-role logins seeded by TestAccountSeeder.
 * They have served their purpose: the owner has seen what each role
 * sees and settled on the roles worth keeping, so six accounts that
 * waive two-factor authentication no longer earn their place on a site
 * holding the congregation's personal details.
 *
 * The seeder and the users.is_test_account flag both stay, so another
 * round of them is one artisan command away.
 */
return new class extends Migration
{
    /**
     * Delete every flagged account together with the roster record the
     * seeder gave it.
     */
    public function up(): void
    {
        $accounts = DB::table('users')->where('is_test_account', true)->pluck('id');

        if ($accounts->isEmpty()) {
            return;
        }

        /**
         * members.user_id is nullOnDelete, so the roster rows outlive
         * their login unless they are removed first. Only the seeder's
         * own unpublished records are touched, matched by the link.
         */
        DB::table('members')->whereIn('user_id', $accounts)->delete();

        /** Role assignments cascade with the user's row only through the roles side, not the model side. */
        DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->whereIn('model_id', $accounts)
            ->delete();

        DB::table('users')->whereIn('id', $accounts)->delete();
    }

    /**
     * Deliberately a no-op: the accounts were created with passwords
     * generated once and never stored, so re-inserting rows would
     * produce logins nobody can use. Re-run TestAccountSeeder instead.
     */
    public function down(): void {}
};
