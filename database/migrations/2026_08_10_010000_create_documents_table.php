<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * Church documents (교회 문서): the forms and policies the office hands
 * out, kept beside the 주보 in the new 자료실.
 *
 * Like a bulletin these default to 성도 전용, since a registration card
 * or an expense form is for the congregation rather than the internet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('file_path');
            $table->date('published_at');
            $table->boolean('is_members_only')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        if (! Schema::hasTable('permissions')) {
            return;
        }

        /** Grant the new resource to whoever already manages 소식. */
        $now = now();

        foreach (DB::table('permissions')->where('name', 'like', '%:Announcement')->get() as $permission) {
            $name = str_replace(':Announcement', ':Document', $permission->name);

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

    public function down(): void
    {
        Schema::dropIfExists('documents');

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('name', 'like', '%:Document')->delete();
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
};
