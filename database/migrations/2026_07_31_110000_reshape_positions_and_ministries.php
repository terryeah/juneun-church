<?php

use App\Models\Member;
use App\Models\Ministry;
use App\Models\Position;
use App\Models\StaffMember;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Positions lose their unused category column, gain 성도, and follow
 * Presbyterian rank order. 협력선교사 simplifies to 선교사. Ministries
 * gain a free-text description; the old 일본 department becomes the
 * 선교팀 ministry with 일본 recorded as its mission field.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('ministries', function (Blueprint $table) {
            $table->string('description')->nullable()->after('name');
        });

        Position::query()->where('name', '협력선교사')->update(['name' => '선교사']);

        $order = ['담임목사', '부목사', '선교사', '전도사', '장로', '권사', '집사', '봉사자', '성도'];

        foreach ($order as $index => $name) {
            Position::query()->updateOrCreate(['name' => $name], ['sort_order' => ($index + 1) * 10]);
        }

        $missions = Ministry::query()->firstOrCreate(
            ['name' => '선교팀'],
            ['sort_order' => (int) Ministry::query()->max('sort_order') + 1],
        );
        $missions->update(['description' => '일본']);

        StaffMember::query()->where('department', '일본')->update(['department' => '선교팀']);
        Member::query()->where('department', '일본')->update(['department' => '선교팀']);
        Ministry::query()->where('name', '일본')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->string('category')->default('volunteer');
        });

        Schema::table('ministries', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
