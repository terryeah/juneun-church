<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Records the 2026-07-19 Sunday offering as printed in the 26 July
 * bulletin (지난 주 인원 및 헌금 통계). Entered as data because the
 * bulletin predates the offerings feature going live.
 */
return new class extends Migration
{
    /**
     * Insert the offering unless that Sunday is already recorded.
     */
    public function up(): void
    {
        $exists = DB::table('offerings')->where('sunday_date', '2026-07-19')->exists();

        if ($exists) {
            return;
        }

        DB::table('offerings')->insert([
            'sunday_date' => '2026-07-19',
            'items' => json_encode([
                ['category' => '주일헌금', 'name' => null, 'amount' => '1112.20'],
                ['category' => '감사헌금', 'name' => null, 'amount' => '190'],
                ['category' => '십일조', 'name' => null, 'amount' => '4017'],
                ['category' => '선교헌금', 'name' => '일본선교', 'amount' => '90'],
                ['category' => '목적헌금', 'name' => '새가족환영회', 'amount' => '348'],
            ], JSON_UNESCAPED_UNICODE),
            'note' => null,
            'created_by' => DB::table('users')->orderBy('id')->value('id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Remove the seeded offering record.
     */
    public function down(): void
    {
        DB::table('offerings')->where('sunday_date', '2026-07-19')->delete();
    }
};
