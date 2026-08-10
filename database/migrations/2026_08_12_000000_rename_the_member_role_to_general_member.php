<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Renames the permissionless 'member' role to 'general_member'.
 *
 * 성도 is no longer something a role says. Whether somebody is one of
 * the church's own is answered by the 교적 record their account is
 * linked to, which is the only place that has ever really known - the
 * 가입 신청 flow has always linked one on approval. That leaves the
 * role meaning what it actually did all along: an account that signs
 * in and runs nothing, 일반회원.
 *
 * The distinction matters now that a 가입 신청 can be approved without
 * putting the applicant on the 교적. Somebody who attends but is not
 * registered gets an account and the public site; 성도 전용 content
 * stays with the people on the roster.
 */
return new class extends Migration
{
    /**
     * Rename the role, keeping every account assigned to it.
     *
     * Spatie holds the assignment by role id, so renaming the row moves
     * everyone across without touching model_has_roles.
     */
    public function up(): void
    {
        DB::table('roles')->where('name', 'member')->update(['name' => 'general_member']);
    }

    /**
     * Put the old name back.
     */
    public function down(): void
    {
        DB::table('roles')->where('name', 'general_member')->update(['name' => 'member']);
    }
};
