<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Events gain an optional end time and album event dates become
 * optional, since not every album maps to a dated event.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->time('end_time')->nullable()->after('end_date');
        });

        Schema::table('albums', function (Blueprint $table) {
            $table->date('event_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('end_time');
        });

        Schema::table('albums', function (Blueprint $table) {
            $table->date('event_date')->nullable(false)->change();
        });
    }
};
