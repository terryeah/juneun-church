<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * The congregation roster (교적부). Members are grouped into households
 * through head_id and carry the usual roster fields: baptism, position,
 * ministry, and standing. Permissions go to admin-level roles only,
 * since the roster holds personal data.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('gender')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('photo')->nullable();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->string('department')->nullable();
            $table->string('baptism_type')->nullable();
            $table->date('baptism_date')->nullable();
            $table->string('status')->default('재적');
            $table->date('registered_at')->nullable();
            $table->foreignId('head_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('relationship')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        if (! Schema::hasTable('permissions')) {
            return;
        }

        $now = now();
        $actions = ['ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny',
            'ForceDelete', 'ForceDeleteAny', 'Restore', 'RestoreAny', 'Reorder', 'Replicate'];
        $roleIds = DB::table('roles')
            ->whereIn('name', ['developer', 'admin', 'super_admin'])
            ->pluck('id');

        foreach ($actions as $action) {
            $id = DB::table('permissions')->insertGetId([
                'name' => "{$action}:Member",
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

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
            DB::table('permissions')->where('name', 'like', '%:Member')->delete();
        }

        Schema::dropIfExists('members');
    }
};
