<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the department that looks after the church's offerings.
 *
 * 재정부 is the usual name for it in Korean churches, and is the one
 * that matches "헌금 관리". The 26 July bulletin's order of service
 * names 헌금위원 against 봉헌, but that is the serving role that
 * collects the offering during the service rather than the department
 * that counts and manages it. Rename it in the admin if the church
 * uses a different word.
 */
return new class extends Migration
{
    /**
     * The department name to add.
     */
    protected string $name = '재정부';

    /**
     * Insert it unless a department of that name already exists.
     */
    public function up(): void
    {
        if (DB::table('ministries')->where('name', $this->name)->exists()) {
            return;
        }

        DB::table('ministries')->insert([
            'name' => $this->name,
            'description' => '헌금을 집계하고 교회 재정을 관리합니다.',
            'sort_order' => (int) DB::table('ministries')->max('sort_order') + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Remove it while no member has been assigned to it.
     */
    public function down(): void
    {
        DB::table('ministries')
            ->where('name', $this->name)
            ->whereNotExists(fn ($query) => $query->select(DB::raw(1))
                ->from('members')
                ->whereColumn('members.department', 'ministries.name'))
            ->delete();
    }
};
