<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the youth and children's departments (청년부, 학생부, 유치부,
 * 유아부) as ministries, skipping any name that already exists.
 */
return new class extends Migration
{
    /**
     * The departments to insert, keyed by name.
     *
     * @var array<string, string>
     */
    private const DEPARTMENTS = [
        '청년부' => '고등학교 졸업 후 청년 세대 모임',
        '학생부' => '중고등학생 모임',
        '유치부' => '유치원, 초등학생 어린이 모임',
        '유아부' => '영유아와 부모가 함께하는 모임',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sortOrder = (int) DB::table('ministries')->max('sort_order');

        foreach (self::DEPARTMENTS as $name => $description) {
            if (DB::table('ministries')->where('name', $name)->exists()) {
                continue;
            }

            DB::table('ministries')->insert([
                'name' => $name,
                'description' => $description,
                'sort_order' => ++$sortOrder,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('ministries')->whereIn('name', array_keys(self::DEPARTMENTS))->delete();
    }
};
