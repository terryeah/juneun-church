<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Closes the notices already on the open web that name people.
 *
 * The switch is no use to the three notices that prompted it unless
 * they are actually flipped: 새가족 수료식 and 셀 배정 print members' full
 * names, and 6차 반찬나눔 prints the 봉사부장 to contact. Everything else
 * on the site is left public, and the update goes through the query
 * builder so the model's highlight hook is not woken by a data fix.
 */
return new class extends Migration
{
    /**
     * Slugs of the seeded notices that name individual members.
     *
     * @var list<string>
     */
    private const SLUGS = ['news-20260802-2', 'news-20260809', 'news-20260809-2'];

    /**
     * Restrict them to signed-in 성도.
     */
    public function up(): void
    {
        DB::table('announcements')->whereIn('slug', self::SLUGS)->update(['is_members_only' => true]);
    }

    /**
     * Return them to the open web.
     */
    public function down(): void
    {
        DB::table('announcements')->whereIn('slug', self::SLUGS)->update(['is_members_only' => false]);
    }
};
