<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Records that a 가입 신청 has been redacted, on the row itself.
 *
 * membership:redact decided whether a row was already done by reading
 * its name back and comparing it with '지움' - a value the applicant
 * types into the public sign-up form. Anyone submitting 이름 = 지움 was
 * therefore skipped by the redaction pass for ever, keeping their birth
 * date, phone number, email address and password hash indefinitely,
 * while every screen that asks isRedacted() reported the opposite and
 * said the details had been erased.
 *
 * The sentinel stays as what a redacted field reads as. What it stops
 * being is the record of whether the redaction happened.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('membership_requests', function (Blueprint $table) {
            $table->timestamp('redacted_at')->nullable()->after('reviewed_at');
        });

        /**
         * Rows already redacted by an earlier run are stamped so they
         * are not redacted a second time. reviewed_at is when the
         * details were still there, but it is the only honest date the
         * row carries and it is the one the retention window is counted
         * from anyway.
         */
        DB::table('membership_requests')
            ->where('name', '지움')
            ->whereNull('redacted_at')
            ->update(['redacted_at' => DB::raw('reviewed_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_requests', function (Blueprint $table) {
            $table->dropColumn('redacted_at');
        });
    }
};
