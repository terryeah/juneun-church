<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records how the reviewing administrator confirmed that the applicant
 * really is the person they claim to be.
 *
 * A candidate on the review screen only ever showed that the applicant's
 * own claims agreed with a roster record, which a stranger who knows a
 * member's name can arrange. Approval hands out a login that can read
 * the giving page, so the check has to be deliberate and has to leave a
 * trace that can be audited afterwards.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('membership_requests', function (Blueprint $table) {
            $table->string('verification_method')->nullable()->after('reviewed_at');
            $table->text('verification_note')->nullable()->after('verification_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_requests', function (Blueprint $table) {
            $table->dropColumn(['verification_method', 'verification_note']);
        });
    }
};
