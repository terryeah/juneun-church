<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops 활성화 from 앨범.
 *
 * The flag was an audience decision when 앨범 was open to the street:
 * an album was on the shelf or it was not, and that was the whole of
 * what a stranger could reach. 앨범 became 성도 전용 as a page, gated by
 * the reader's 교적 record, and the flag stopped being about audience
 * the same day - Album::scopeVisible already said as much in a comment.
 *
 * What it still did was curate: six albums held pictures the site uses
 * elsewhere - the 대표 사진, the home band graphics - and stayed off the
 * shelf while doing it. The church would rather every 성도 saw
 * everything the church has, those six included, so the curation goes
 * with the audience decision and the column follows both.
 */
return new class extends Migration
{
    /**
     * Remove the flag; every album is on the shelf now.
     */
    public function up(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->dropColumn('is_published');
        });
    }

    /**
     * Put the flag back, on for every album.
     *
     * The column was created defaulting to false, but reversing this
     * migration must not empty the shelf: the six albums that were off
     * it are not recoverable from here, and defaulting to false would
     * hide the other thirty-two as well.
     */
    public function down(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->boolean('is_published')->default(true)->after('cover_photo_path');
        });
    }
};
