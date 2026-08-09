<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets an album be restricted to signed-in 성도.
 *
 * Unlike a 주보, most albums are church life a visitor is welcome to
 * browse, so the column defaults to open and restriction is opt-in:
 * only the sets a photographer flags - a retreat, a baptism, anything
 * showing members close up - are kept behind the sign-in.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->boolean('is_members_only')->default(false)->after('is_published');
        });
    }

    public function down(): void
    {
        Schema::table('albums', function (Blueprint $table) {
            $table->dropColumn('is_members_only');
        });
    }
};
