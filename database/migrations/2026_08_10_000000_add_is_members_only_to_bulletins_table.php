<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Restricts 주보 to signed-in 성도.
 *
 * A bulletin carries the week's cell assignments, the names of those
 * serving and the offering record, so it is treated the way the notices
 * naming members already are. The column defaults to restricted rather
 * than open: a bulletin uploaded without a thought fails closed, and
 * anything genuinely public is opened deliberately.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bulletins', function (Blueprint $table) {
            $table->boolean('is_members_only')->default(true)->after('published_at');
        });

        DB::table('bulletins')->update(['is_members_only' => true]);
    }

    public function down(): void
    {
        Schema::table('bulletins', function (Blueprint $table) {
            $table->dropColumn('is_members_only');
        });
    }
};
