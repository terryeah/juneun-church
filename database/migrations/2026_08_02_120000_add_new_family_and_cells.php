<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * Cell small groups (셀) and the 새가족 수료 date. Each member may belong
 * to one cell; a cell has an optional leader (셀장) drawn from the
 * roster. Permissions mirror the Ministry resource for the same roles.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cells', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('leader_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('members', function (Blueprint $table) {
            $table->date('new_family_completed_at')->nullable()->after('registered_at');
            $table->foreignId('cell_id')->nullable()->after('head_id')->constrained()->nullOnDelete();
        });

        if (! Schema::hasTable('permissions')) {
            return;
        }

        $now = now();

        foreach (DB::table('permissions')->where('name', 'like', '%:Ministry')->get() as $permission) {
            $name = str_replace(':Ministry', ':Cell', $permission->name);

            $id = DB::table('permissions')->insertGetId([
                'name' => $name,
                'guard_name' => $permission->guard_name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach (DB::table('role_has_permissions')->where('permission_id', $permission->id)->pluck('role_id') as $roleId) {
                DB::table('role_has_permissions')->insert(['permission_id' => $id, 'role_id' => $roleId]);
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
            DB::table('permissions')->where('name', 'like', '%:Cell')->delete();
        }

        Schema::table('members', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cell_id');
            $table->dropColumn('new_family_completed_at');
        });

        Schema::dropIfExists('cells');
    }
};
