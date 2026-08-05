<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets a 교회 소식 carry the home page 하이라이트 section.
 *
 * Only one announcement holds the flag at a time; the model clears it
 * from the previous holder on save, so the column is indexed for the
 * single-row lookup the home page makes on every request.
 */
return new class extends Migration
{
    /**
     * Add the highlight flag.
     */
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->boolean('is_highlighted')->default(false)->index()->after('is_pinned');
        });
    }

    /**
     * Drop the highlight flag.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex(['is_highlighted']);
            $table->dropColumn('is_highlighted');
        });
    }
};
