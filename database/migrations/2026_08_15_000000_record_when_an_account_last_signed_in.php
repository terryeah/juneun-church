<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records when each account was last used.
 *
 * The account list shows when a login was created but never when it was
 * last opened, so there is no way to tell an account somebody uses every
 * Sunday from one belonging to a volunteer who left two years ago. The
 * sign-ins are in the activity log, but only a developer may read it -
 * which puts the pastor in the position of having to ask somebody else
 * who still has a key to the congregation's records.
 *
 * Revoking an account is already a switch on the 성도 form. This is the
 * part that was missing: knowing which one to revoke.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('last_login_at')->nullable()->after('is_audit_exempt');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('last_login_at');
        });
    }
};
