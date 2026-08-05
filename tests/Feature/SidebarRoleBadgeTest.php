<?php

namespace Tests\Feature;

use App\Filament\Pages\Analytics;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\DatabaseGraph;
use App\Filament\Resources\Activities\ActivityResource;
use App\Filament\Resources\Albums\AlbumResource;
use App\Filament\Resources\Announcements\AnnouncementResource;
use App\Filament\Resources\Cells\CellResource;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\MembershipRequests\MembershipRequestResource;
use App\Filament\Resources\Ministries\MinistryResource;
use App\Filament\Resources\Offerings\OfferingResource;
use App\Filament\Resources\PersonalOfferings\PersonalOfferingResource;
use App\Filament\Resources\Photos\PhotoResource;
use App\Filament\Resources\Positions\PositionResource;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\ServiceTypes\ServiceTypeResource;
use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Filament\Resources\StaffMembers\StaffMemberResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\MembershipRequest;
use App\Models\User;
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
     * The activity log and the role editor are developer-only, the
     * offering resources are admin-only, and announcements reach
     * content editors as well.
     */
    public function test_badges_follow_the_seeded_view_any_grants(): void
    {
        $this->assertSame('developer', AdminPanelProvider::roleBadge(ActivityResource::class));
        $this->assertSame('developer', AdminPanelProvider::roleBadge(RoleResource::class));
        $this->assertSame('admin', AdminPanelProvider::roleBadge(OfferingResource::class));
        $this->assertSame('admin', AdminPanelProvider::roleBadge(PersonalOfferingResource::class));
        $this->assertNull(AdminPanelProvider::roleBadge(AnnouncementResource::class));
    }

    /**
     * What a content editor needs to publish carries no badge: the
     * gallery, and the service types and ministries that a sermon or
     * an announcement has to be filed under.
     */
    public function test_publishing_work_reaches_content_editors(): void
    {
        $this->assertNull(AdminPanelProvider::roleBadge(ServiceTypeResource::class));
        $this->assertNull(AdminPanelProvider::roleBadge(MinistryResource::class));
        $this->assertNull(AdminPanelProvider::roleBadge(AlbumResource::class));
        $this->assertNull(AdminPanelProvider::roleBadge(PhotoResource::class));
    }

    /**
     * Personal details, logins and money stay with administrators.
     *
     * The whole 공동체 group stays here because a 성도 record holds a
     * birth date, a phone number, an address and an email address, and
     * 셀 groups those people. 사이트 설정 holds the giving account
     * numbers and 직분 the church's order of office, so a mistake in
     * either reaches the congregation rather than the website.
     */
    public function test_the_remaining_resources_stay_with_administrators(): void
    {
        foreach ([
            MemberResource::class,
            CellResource::class,
            UserResource::class,
            StaffMemberResource::class,
            SiteSettingResource::class,
            PositionResource::class,
            MembershipRequestResource::class,
            OfferingResource::class,
            PersonalOfferingResource::class,
        ] as $resource) {
            $this->assertSame('admin', AdminPanelProvider::roleBadge($resource), $resource.' changed audience.');
        }
    }

    /**
     * A resource that counts something keeps that count, and wears the
     * role tag beside it rather than under it.
     *
     * The count stays in Filament's own badge - the single one a
     * sidebar item has - and the role tag is carried by the custom
     * properties the pseudo-element in the panel's stylesheet draws
     * from, which is why the two no longer compete for the same slot.
     */
    public function test_a_pending_count_and_the_role_tag_render_together(): void
    {
        Filament::setCurrentPanel('admin');
        $this->actingAs($this->superAdmin());

        MembershipRequest::create([
            'name' => '김주은',
            'birth_date' => '1990-01-01',
            'phone' => '0400 000 000',
            'email' => 'juneun@example.com',
            'password' => 'secret-password',
        ]);

        [$item] = AdminPanelProvider::accessibleItems(MembershipRequestResource::class);

        $this->assertSame('1', $item->getBadge());
        $this->assertStringContainsString("--role-badge:'admin'", $item->getExtraAttributeBag()->get('style'));
    }

    /**
     * Either half stands on its own: a resource with a role but nothing
     * to count shows the tag alone, and a resource with neither is left
     * completely bare.
     */
    public function test_each_half_of_the_badge_degrades_on_its_own(): void
    {
        Filament::setCurrentPanel('admin');
        $this->actingAs($this->superAdmin());

        [$users] = AdminPanelProvider::accessibleItems(UserResource::class);

        $this->assertNull($users->getBadge());
        $this->assertStringContainsString("--role-badge:'admin'", $users->getExtraAttributeBag()->get('style'));

        [$announcements] = AdminPanelProvider::accessibleItems(AnnouncementResource::class);

        $this->assertNull($announcements->getBadge());
        $this->assertNull($announcements->getExtraAttributeBag()->get('style'));
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

    /**
     * An account that clears every resource's authorisation check, so
     * the navigation items under test are actually built.
     */
    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->syncRoles(['super_admin']);

        return $user;
    }
}
