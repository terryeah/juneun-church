<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the per-record 성도 전용 flag from every table that carried one.
 *
 * 교회 소식, 자료실 and 앨범 are 성도 전용 as whole pages now, gated by
 * the reader's 교적 record, and the panel's toggles went with the
 * change. The columns outlived them: nobody could set one, yet three
 * announcements were still holding a flag that quietly kept them off
 * the home page's 최신 소식 and out of the 하이라이트, with nothing in the
 * panel to say why.
 *
 * The activity log keeps its rows, and ActivityChanges keeps the label
 * for the key, so a historical change still reads as 성도 전용.
 */
return new class extends Migration
{
    /**
     * Drop the flag wherever it was added.
     */
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex(['is_members_only']);
            $table->dropColumn('is_members_only');
        });

        foreach (['bulletins', 'documents', 'albums'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn('is_members_only');
            });
        }
    }

    /**
     * Put the flag back, each with the default it was created with.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->boolean('is_members_only')->default(false)->index()->after('is_highlighted');
        });

        Schema::table('bulletins', function (Blueprint $table) {
            $table->boolean('is_members_only')->default(true)->after('published_at');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->boolean('is_members_only')->default(true)->after('published_at');
        });

        Schema::table('albums', function (Blueprint $table) {
            $table->boolean('is_members_only')->default(false)->after('is_published');
        });
    }
};
