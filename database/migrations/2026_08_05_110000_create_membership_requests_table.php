<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * Public sign-up requests (가입 신청) awaiting administrator approval.
 * A name alone cannot tell 동명이인 apart, so nothing is linked
 * automatically: an administrator matches the applicant against the
 * roster and presses 승인 before any login exists.
 *
 * Permissions mirror the roster's, because a request carries the same
 * personal data. The least-privileged 'member' role approved
 * applicants receive is created here with no permissions at all.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('membership_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('birth_date');
            $table->string('phone');
            $table->string('email');
            $table->string('password');
            $table->text('note')->nullable();
            $table->string('status')->default('대기');
            $table->foreignId('matched_member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        if (! Schema::hasTable('permissions')) {
            return;
        }

        $now = now();

        DB::table('roles')->insertOrIgnore([
            'name' => 'member',
            'guard_name' => 'web',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach (DB::table('permissions')->where('name', 'like', '%:Member')->get() as $permission) {
            $name = str_replace(':Member', ':MembershipRequest', $permission->name);

            $id = DB::table('permissions')->insertGetId([
                'name' => $name,
                'guard_name' => $permission->guard_name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach (DB::table('role_has_permissions')->where('permission_id', $permission->id)->pluck('role_id') as $roleId) {
                DB::table('role_has_permissions')->insert(['permission_id' => $id, 'role_id' => $roleId]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('name', 'like', '%:MembershipRequest')->delete();
            DB::table('roles')->where('name', 'member')->delete();
        }

        Schema::dropIfExists('membership_requests');
    }
};
