<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a photo album be read in its own order without sorting it.
 *
 * Photos are always fetched by album and always ordered by sort_order,
 * but the only index is on album_id alone, so every read of an album
 * ends in a filesort. In the church's largest album that is 806 rows
 * sorted to hand back 24 - and the gallery fetches that album 34 times
 * as somebody scrolls to the end of it.
 *
 * It has not hurt yet: 806 rows sort inside the 256 KB sort buffer in
 * well under a millisecond. The line worth knowing is where the sort
 * spills to a temporary file on disk, which for this table's row width
 * is around 1,300 photos in one album - and the church is at 806.
 *
 * The videos table was given this index when it was created. This is
 * the same index on the table that has needed it all along, and it
 * costs about 40 KB.
 */
return new class extends Migration
{
    /**
     * Add the composite the photo queries actually use.
     */
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table): void {
            $table->index(['album_id', 'sort_order']);
        });
    }

    /**
     * Drop it again.
     */
    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table): void {
            $table->dropIndex(['album_id', 'sort_order']);
        });
    }
};
