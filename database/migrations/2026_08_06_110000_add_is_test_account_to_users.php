<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Marks the throwaway per-role accounts used to check what each role
 * sees. They skip mandatory two-factor authentication, because setting
 * up an authenticator app six times over defeats the point of them.
 */
return new class extends Migration
{
    /**
     * Add the flag and set it on the existing test accounts.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_test_account')->default(false)->after('email');
        });

        DB::table('users')
            ->where('email', 'like', 'test-%@juneun.com')
            ->update(['is_test_account' => true]);
    }

    /**
     * Drop the flag.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_test_account');
        });
    }
};
