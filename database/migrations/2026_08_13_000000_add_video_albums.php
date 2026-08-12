<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * Lets an album hold videos instead of photographs.
 *
 * An album already knows how to be published, restricted to 성도, dated
 * and titled, and the gallery already knows how to list one. A video
 * album is the same thing with different contents, so it is the same
 * table with a 종류 rather than a parallel set of everything.
 *
 * The videos themselves are YouTube identifiers. Nothing is uploaded
 * and nothing is stored on the server or in R2: the church's channel
 * holds the file, and this holds the reference to it.
 */
return new class extends Migration
{
    /**
     * Add the album kind and the table of videos.
     */
    public function up(): void
    {
        Schema::table('albums', function (Blueprint $table): void {
            $table->string('type', 16)->default('photo')->after('slug')->index();
        });

        Schema::create('videos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();

            /**
             * A YouTube identifier is eleven characters today. The
             * column is wider so a future change of theirs cannot
             * truncate one into a different video.
             */
            $table->string('youtube_id', 32);
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['album_id', 'sort_order']);
        });

        if (! Schema::hasTable('permissions')) {
            return;
        }

        /**
         * Grant the new resource to whoever already manages 사진.
         *
         * Without this the 동영상 screen has no permissions at all, and
         * Shield's enforced policy answers no to everybody - including
         * the people whose job it is.
         */
        $now = now();

        foreach (DB::table('permissions')->where('name', 'like', '%:Photo')->get() as $permission) {
            $name = str_replace(':Photo', ':Video', $permission->name);

            if (DB::table('permissions')->where('name', $name)->exists()) {
                continue;
            }

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
     * Drop them again.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');

        Schema::table('albums', function (Blueprint $table): void {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });
    }
};
