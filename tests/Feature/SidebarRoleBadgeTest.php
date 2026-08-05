<?php

namespace Tests\Feature;

use App\Filament\Pages\Analytics;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\DatabaseGraph;
use App\Filament\Resources\Activities\ActivityResource;
use App\Filament\Resources\Albums\AlbumResource;
use App\Filament\Resources\Announcements\AnnouncementResource;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\Ministries\MinistryResource;
use App\Filament\Resources\Offerings\OfferingResource;
use App\Filament\Resources\PersonalOfferings\PersonalOfferingResource;
use App\Filament\Resources\Photos\PhotoResource;
use App\Filament\Resources\Positions\PositionResource;
use App\Filament\Resources\ServiceTypes\ServiceTypeResource;
use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Filament\Resources\StaffMembers\StaffMemberResource;
use App\Filament\Resources\Users\UserResource;
use App\Providers\Filament\AdminPanelProvider;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The sidebar role badge is derived from the seeded ViewAny grants, so
 * these assertions break the moment a resource's audience changes.
 */
class SidebarRoleBadgeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the roles and the Shield-style permissions of every panel
     * resource, then apply the role matrix.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            Permission::findOrCreate('ViewAny:'.class_basename($resource::getModel()), 'web');
        }

        $this->seed(RolePermissionSeeder::class);
    }

    /**
     * The activity log is developer-only, the offering resources are
     * admin-only, and announcements reach content editors as well.
     */
    public function test_badges_follow_the_seeded_view_any_grants(): void
    {
        $this->assertSame('developer', AdminPanelProvider::roleBadge(ActivityResource::class));
        $this->assertSame('admin', AdminPanelProvider::roleBadge(OfferingResource::class));
        $this->assertSame('admin', AdminPanelProvider::roleBadge(PersonalOfferingResource::class));
        $this->assertNull(AdminPanelProvider::roleBadge(AnnouncementResource::class));
    }

    /**
     * The three reference resources belong to content editors as well,
     * so none of them carries a badge.
     */
    public function test_reference_data_reaches_content_editors(): void
    {
        $this->assertNull(AdminPanelProvider::roleBadge(SiteSettingResource::class));
        $this->assertNull(AdminPanelProvider::roleBadge(ServiceTypeResource::class));
        $this->assertNull(AdminPanelProvider::roleBadge(MinistryResource::class));
    }

    /**
     * Personal details, logins and money stay with administrators.
     *
     * 앨범 and 사진 are here because retiring media_coordinator and
     * contributor left nobody below an administrator holding them, not
     * because anyone asked for the gallery to be locked down.
     */
    public function test_the_remaining_resources_stay_with_administrators(): void
    {
        foreach ([
            MemberResource::class,
            UserResource::class,
            StaffMemberResource::class,
            OfferingResource::class,
            PersonalOfferingResource::class,
            PositionResource::class,
            AlbumResource::class,
            PhotoResource::class,
        ] as $resource) {
            $this->assertSame('admin', AdminPanelProvider::roleBadge($resource), $resource.' changed audience.');
        }
    }

    /**
     * Pages hold no model, so their badge comes from the explicit map
     * rather than from any granted permission.
     */
    public function test_pages_carry_their_declared_badges(): void
    {
        $this->assertSame('admin', AdminPanelProvider::roleBadge(Analytics::class));
        $this->assertSame('developer', AdminPanelProvider::roleBadge(DatabaseGraph::class));
        $this->assertNull(AdminPanelProvider::roleBadge(Dashboard::class));
    }
}
