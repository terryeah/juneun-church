<?php

namespace Tests\Feature;

use App\Filament\Resources\Cells\Pages\CreateCell;
use App\Filament\Resources\Members\Pages\CreateMember;
use App\Models\Cell;
use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Covers the roster admin: the serving-scope exclusions and the member
 * and cell create pages rendering without error.
 */
class MemberAdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Lay positions (성도, 집사, 권사, 장로) never count as serving,
     * even with a department filled; other positions and members with
     * only a department still do.
     */
    public function test_lay_positions_are_excluded_from_the_serving_scope(): void
    {
        $deacon = Member::factory()->create([
            'position_id' => Position::factory()->create(['name' => '집사'])->id,
            'department' => '찬양팀',
        ]);
        $missionary = Member::factory()->create([
            'position_id' => Position::factory()->create(['name' => '선교사'])->id,
        ]);
        $departmentOnly = Member::factory()->create(['department' => '안내팀']);
        $plain = Member::factory()->create();

        $serving = Member::query()->serving()->pluck('id');

        $this->assertFalse($serving->contains($deacon->id));
        $this->assertTrue($serving->contains($missionary->id));
        $this->assertTrue($serving->contains($departmentOnly->id));
        $this->assertFalse($serving->contains($plain->id));
    }

    /**
     * A cell is named after its leader (이름 + ' 셀'), follows a leader
     * change, and keeps the last name when the leader is deleted.
     */
    public function test_cell_name_is_derived_from_its_leader(): void
    {
        $leader = Member::factory()->create(['name' => '최윤영']);
        $cell = Cell::create(['leader_id' => $leader->id]);

        $this->assertSame('최윤영 셀', $cell->name);

        $newLeader = Member::factory()->create(['name' => '김한별']);
        $cell->update(['leader_id' => $newLeader->id]);

        $this->assertSame('김한별 셀', $cell->name);

        $newLeader->delete();

        $this->assertSame('김한별 셀', $cell->fresh()->name);
    }

    /**
     * The create pages render, guarding the form schemas against
     * missing imports that only surface at render time.
     */
    public function test_member_and_cell_create_pages_render(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('super_admin');

        foreach (['ViewAny:Member', 'Create:Member', 'ViewAny:Cell', 'Create:Cell'] as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            $user->givePermissionTo($permission);
        }

        Livewire::actingAs($user)->test(CreateMember::class)->assertSuccessful();
        Livewire::actingAs($user)->test(CreateCell::class)->assertSuccessful();
    }
}
