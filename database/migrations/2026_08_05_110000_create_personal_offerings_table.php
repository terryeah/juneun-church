<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * Individual giving records (개인 헌금), kept per Sunday alongside the
 * weekly totals. The giver is linked to the roster when they are on it
 * and otherwise only named. Permissions mirror the weekly offering.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personal_offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offering_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('category');
            $table->decimal('amount', 10, 2);
            $table->string('note')->nullable();
            $table->timestamps();
        });

        if (! Schema::hasTable('permissions')) {
            return;
        }

        $now = now();

        foreach (DB::table('permissions')->where('name', 'like', '%:Offering')->get() as $permission) {
            $name = str_replace(':Offering', ':PersonalOffering', $permission->name);

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
            DB::table('permissions')->where('name', 'like', '%:PersonalOffering')->delete();
        }

        Schema::dropIfExists('personal_offerings');
    }
};
