<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The 교회 소식 listing renders its rows.
 *
 * The panel smoke test only proves the page loads with no records, so
 * the per-record state closure behind the mobile badges goes untested
 * there. This renders an actual row instead.
 */
class AnnouncementsTableTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed roles and permissions, then sign in as a super admin.
     */
    private function admin(): User
    {
        $this->seed(RoleSeeder::class);

        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            $model = class_basename($resource::getModel());

            foreach (['ViewAny', 'View', 'Create', 'Update', 'Delete'] as $prefix) {
                Permission::findOrCreate("{$prefix}:{$model}", 'web');
            }
        }

        $this->seed(RolePermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        return $admin;
    }

    /**
     * A row carries its title and the badges a phone shows in place of
     * the unlabelled tick columns. 성도 전용 is not one of them: the
     * whole 교회 소식 page is behind the 교적 now, so the per-notice flag
     * has nothing left to say in the panel.
     */
    public function test_a_row_renders_its_title_and_state_badges(): void
    {
        Announcement::factory()->pinned()->create([
            'title' => '셀 모임 안내',
            'slug' => 'news-cell-meeting',
            'is_published' => true,
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/announcements')
            ->assertOk()
            ->assertSee('셀 모임 안내')
            ->assertSee('고정')
            ->assertDontSee('성도 전용');
    }

    /**
     * An unpublished notice reads 비공개 rather than 게시.
     */
    public function test_an_unpublished_row_reads_as_private(): void
    {
        Announcement::factory()->create([
            'title' => '작성 중인 소식',
            'slug' => 'news-draft',
            'is_published' => false,
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/announcements')
            ->assertOk()
            ->assertSee('비공개');
    }
}
