<?php

use App\Models\Ministry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * Ministries (부서 / 사역) become their own lookup table so the staff
 * form offers a dropdown instead of free text. Seeds the initial teams
 * plus any department values already in use, and mirrors the Position
 * permissions onto Ministry for the same roles.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ministries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $names = collect(['찬양팀', '주차봉사팀', '안내팀'])
            ->merge(DB::table('staff_members')->distinct()->pluck('department')->filter())
            ->unique()
            ->values();

        foreach ($names as $index => $name) {
            Ministry::query()->create(['name' => $name, 'sort_order' => $index]);
        }

        if (! Schema::hasTable('permissions')) {
            return;
        }

        $now = now();

        foreach (DB::table('permissions')->where('name', 'like', '%:Position')->get() as $permission) {
            $name = str_replace(':Position', ':Ministry', $permission->name);

            $id = DB::table('permissions')->insertGetId([
                'name' => $name,
                'guard_name' => $permission->guard_name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $roleIds = DB::table('role_has_permissions')
                ->where('permission_id', $permission->id)
                ->pluck('role_id');

            foreach ($roleIds as $roleId) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $id,
                    'role_id' => $roleId,
                ]);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('name', 'like', '%:Ministry')->delete();
        }

        Schema::dropIfExists('ministries');
    }
};
