<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a 교회 소식 be published to signed-in 성도 only.
 *
 * Notices such as 새가족 소개 and 셀 배정 name individual members, which
 * has no business on the open web. The flag is off by default so every
 * existing notice keeps the visibility it was written with, and it is
 * indexed because every public announcement query now filters on it.
 */
return new class extends Migration
{
    /**
     * Add the members-only flag.
     */
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->boolean('is_members_only')->default(false)->index()->after('is_highlighted');
        });
    }

    /**
     * Drop the members-only flag.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex(['is_members_only']);
            $table->dropColumn('is_members_only');
        });
    }
};
