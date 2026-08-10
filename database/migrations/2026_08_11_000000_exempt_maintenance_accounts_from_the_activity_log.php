<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Keeps the account that maintains the site out of the activity log.
 *
 * The trail exists to show what the church's own people did. The
 * account doing the building sits in it as the loudest voice by a wide
 * margin - most of the rows on the day this ran were its own - and none
 * of that is congregation activity.
 *
 * The flag is carried on the account rather than worked out from a
 * role, so it stays with the one account it was set on: somebody given
 * the developer role later is logged normally. It is set here from the
 * role only because that is what identifies the account today, without
 * writing a person's name or address into a public repository.
 */
return new class extends Migration
{
    /**
     * Add the flag, then clear what the flagged accounts already left.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_audit_exempt')->default(false)->after('is_test_account');
        });

        $exempt = DB::table('users')
            ->join('model_has_roles', function ($join): void {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', '=', 'App\Models\User');
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'developer')
            ->pluck('users.id');

        if ($exempt->isEmpty()) {
            return;
        }

        DB::table('users')->whereIn('id', $exempt)->update(['is_audit_exempt' => true]);

        /**
         * Sign-ins and page openings are the account's own comings and
         * goings, so they go entirely.
         */
        DB::table('activity_log')
            ->whereIn('log_name', ['auth', 'page'])
            ->whereIn('causer_id', $exempt)
            ->where('causer_type', 'App\Models\User')
            ->delete();

        /**
         * What was created, edited or deleted stays - that history is
         * the point of the log - but it stops carrying a name, which is
         * how the same work will be recorded from now on.
         */
        DB::table('activity_log')
            ->whereIn('causer_id', $exempt)
            ->where('causer_type', 'App\Models\User')
            ->update(['causer_id' => null, 'causer_type' => null]);
    }

    /**
     * Drop the flag.
     *
     * The rows this removed are not recoverable, so rolling back only
     * puts the column away and leaves every account logged again.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_audit_exempt');
        });
    }
};
