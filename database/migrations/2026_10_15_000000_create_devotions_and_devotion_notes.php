<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * 큐티: a day's passage and guide written from the Sunday sermon, and
 * each 성도's own 묵상 on it.
 *
 * The guide is publishing, so it takes the 교회 소식 permissions -
 * administrators and content editors - the way 문서 took them in
 * 2026_08_10_010000_create_documents_table.php. The notes take none:
 * no panel screen reads them, because a 묵상 is seen by its writer and
 * nobody else, the pastor included.
 */
return new class extends Migration
{
    /**
     * Create both tables and grant the guide's permissions.
     */
    public function up(): void
    {
        Schema::create('devotions', function (Blueprint $table) {
            $table->id();
            $table->date('devotion_date')->unique();
            $table->string('title');
            $table->string('scripture');
            $table->text('passage')->nullable();
            $table->longText('content');
            $table->foreignId('sermon_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_published')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('devotion_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->unique(['user_id', 'devotion_id']);
        });

        if (! Schema::hasTable('permissions')) {
            return;
        }

        $now = now();

        foreach (DB::table('permissions')->where('name', 'like', '%:Announcement')->get() as $permission) {
            $id = DB::table('permissions')->insertGetId([
                'name' => str_replace(':Announcement', ':Devotion', $permission->name),
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
     * Drop both tables and the guide's permissions.
     */
    public function down(): void
    {
        Schema::dropIfExists('devotion_notes');
        Schema::dropIfExists('devotions');

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('name', 'like', '%:Devotion')->delete();
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
};
