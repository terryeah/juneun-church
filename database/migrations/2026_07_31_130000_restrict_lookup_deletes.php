<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deleting a position or service type must not silently delete the
 * staff members or sermons attached to it; the delete is now blocked
 * until the records are moved.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('staff_members', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->foreign('position_id')->references('id')->on('positions')->restrictOnDelete();
        });

        Schema::table('sermons', function (Blueprint $table) {
            $table->dropForeign(['service_type_id']);
            $table->foreign('service_type_id')->references('id')->on('service_types')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_members', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->foreign('position_id')->references('id')->on('positions')->cascadeOnDelete();
        });

        Schema::table('sermons', function (Blueprint $table) {
            $table->dropForeign(['service_type_id']);
            $table->foreign('service_type_id')->references('id')->on('service_types')->cascadeOnDelete();
        });
    }
};
