<?php

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The congregation roster becomes the single identity core: serving
 * members (섬김이) and site accounts both hang off member records.
 * Existing staff_members rows and site users are folded into members,
 * then the staff_members table is dropped.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->text('bio')->nullable()->after('department');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
        });

        foreach (DB::table('staff_members')->orderBy('sort_order')->get() as $staff) {
            $member = Member::query()->firstOrNew(['name' => $staff->name]);
            $member->fill([
                'position_id' => $staff->position_id,
                'department' => $staff->department,
                'bio' => $staff->bio,
                'email' => $member->email ?? $staff->email,
                'phone' => $member->phone ?? $staff->phone,
                'photo' => $member->photo ?? $staff->photo,
                'status' => $member->status ?? '재적',
            ]);
            $member->sort_order = $staff->sort_order;
            $member->is_published = (bool) $staff->is_published;
            $member->save();
        }

        foreach (User::query()->get() as $user) {
            $member = Member::query()->firstOrNew(['name' => $user->name]);
            $member->fill([
                'email' => $member->email ?? $user->email,
                'status' => $member->status ?? '재적',
            ]);
            $member->user_id = $user->id;
            $member->save();
        }

        Schema::dropIfExists('staff_members');

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('name', 'like', '%:StaffMember')->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['bio', 'sort_order', 'is_published']);
        });
    }
};
