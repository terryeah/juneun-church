<?php

namespace Tests\Feature;

use App\Filament\Pages\DepartmentOverview;
use App\Filament\Resources\Members\Pages\ListMembers;
use App\Models\Member;
use App\Models\Ministry;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Covers the 부서 현황 page: it renders for a super admin with member
 * counts, and its row links carry a tableFilters query string that the
 * member list actually applies.
 */
class DepartmentOverviewTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The super admin visiting the pages.
     */
    private User $admin;

    /**
     * Seed roles and grant the view permissions the page and the
     * member list guard themselves with.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');

        foreach (['ViewAny:Ministry', 'ViewAny:Member'] as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            $this->admin->givePermissionTo($permission);
        }
    }

    /**
     * The page renders and shows each 부서 with its member count.
     */
    public function test_page_renders_with_member_counts(): void
    {
        $ministry = Ministry::query()->firstOrCreate(['name' => '청년부']);
        Member::factory()->count(2)->create(['department' => $ministry->name]);

        Livewire::actingAs($this->admin)
            ->test(DepartmentOverview::class)
            ->assertSuccessful()
            ->assertSee('청년부')
            ->assertSee('2');
    }

    /**
     * The filters query string a row links to (Filament aliases the
     * tableFilters property to `filters` in the URL) filters the
     * member list to that 부서.
     */
    public function test_department_filter_query_string_filters_the_member_list(): void
    {
        $inside = Member::factory()->create(['department' => '청년부']);
        $outside = Member::factory()->create(['department' => '학생부']);

        Livewire::actingAs($this->admin)
            ->withQueryParams(['filters' => ['department' => ['value' => '청년부']]])
            ->test(ListMembers::class)
            ->assertCanSeeTableRecords([$inside])
            ->assertCanNotSeeTableRecords([$outside]);
    }
}
