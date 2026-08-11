<?php

namespace Tests\Feature;

use App\Filament\Pages\Analytics;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\DatabaseGraph;
use App\Filament\Pages\Wiki;
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
use Spatie\Permission\Models\Role;
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
     * offering resources are named for the department that owns them,
     * and announcements reach content editors as well.
     */
    public function test_badges_follow_the_seeded_view_any_grants(): void
    {
        $this->assertSame('developer', AdminPanelProvider::roleBadge(ActivityResource::class));
        $this->assertSame('developer', AdminPanelProvider::roleBadge(RoleResource::class));
        $this->assertSame('finance_officer', AdminPanelProvider::roleBadge(OfferingResource::class));
        $this->assertSame('finance_officer', AdminPanelProvider::roleBadge(PersonalOfferingResource::class));
        $this->assertSame('content_editor', AdminPanelProvider::roleBadge(AnnouncementResource::class));
    }

    /**
     * What a content editor needs to publish is tagged for them: the
     * gallery, and the service types and ministries that a sermon or
     * an announcement has to be filed under.
     *
     * These carried no tag at all until the sidebar started answering
     * "who is this for" on every line. An untagged line read as an
     * oversight rather than as an answer.
     */
    public function test_publishing_work_reaches_content_editors(): void
    {
        foreach ([
            ServiceTypeResource::class,
            MinistryResource::class,
            AlbumResource::class,
            PhotoResource::class,
        ] as $resource) {
            $this->assertSame('content_editor', AdminPanelProvider::roleBadge($resource), $resource.' changed audience.');
        }
    }

    /**
     * Personal details, logins and money stay with administrators.
     *
     * The whole of 교적 and 계정 stays here because a 성도 record holds
     * a birth date, a phone number, an address and an email address,
     * and 셀 groups those people. 사이트 설정 holds the giving account
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
        $this->assertStringContainsString("--role-badge:'관리자'", $item->getExtraAttributeBag()->get('style'));
    }

    /**
     * The tag reads in Korean while the value roleBadge() returns stays
     * the internal key: 관리자 in the sidebar, 'admin' in the matrix.
     */
    public function test_the_tag_is_drawn_in_korean(): void
    {
        Filament::setCurrentPanel('admin');

        /** The activity log answers to the developer role rather than to a permission. */
        $this->actingAs(tap($this->superAdmin())->assignRole('developer'));

        [$activity] = AdminPanelProvider::accessibleItems(ActivityResource::class);

        $this->assertSame('developer', AdminPanelProvider::roleBadge(ActivityResource::class));
        $this->assertStringContainsString("--role-badge:'개발자'", $activity->getExtraAttributeBag()->get('style'));

        [$offerings] = AdminPanelProvider::accessibleItems(OfferingResource::class);

        /** The tag names the department, not the one person's role. */
        $this->assertStringContainsString("--role-badge:'재정부'", $offerings->getExtraAttributeBag()->get('style'));
        $this->assertStringNotContainsString('finance_officer', $offerings->getExtraAttributeBag()->get('style'));
    }

    /**
     * Each tag keeps its own colour: amber for an administrator menu,
     * green for 재정, blue for the developer screens, grey for the
     * editing work. The four have to stay apart, or the tag stops
     * carrying anything the label does not already say.
     *
     * Grey belongs to the editor because that tag sits on more menus
     * than the other three together: the elevated ones only read as
     * elevated while the ordinary one recedes.
     */
    public function test_each_tag_carries_its_own_colour(): void
    {
        Filament::setCurrentPanel('admin');
        $this->actingAs(tap($this->superAdmin())->assignRole('developer'));

        $hues = [
            MemberResource::class => 'warning',
            OfferingResource::class => 'success',
            ActivityResource::class => 'info',
            AnnouncementResource::class => 'gray',
        ];

        foreach ($hues as $resource => $hue) {
            [$item] = AdminPanelProvider::accessibleItems($resource);

            $this->assertStringContainsString(
                "--role-badge-color:var(--{$hue}-600)",
                $item->getExtraAttributeBag()->get('style'),
                $resource.' lost its colour.',
            );
        }
    }

    /**
     * 재정 담당 reaches the two offering resources and nothing else.
     *
     * That grant is deliberately not counted when a badge is worked
     * out, or those two menus would lose their tag entirely - so the
     * 재정부 tag they wear is stated rather than derived, and this
     * checks both halves: the grant stays narrow, and the tag is the
     * one the override names.
     */
    public function test_the_finance_role_owns_only_the_offering_resources(): void
    {
        $finance = Role::findByName('finance_officer', 'web');

        $this->assertEqualsCanonicalizing(
            ['Offering', 'PersonalOffering'],
            $finance->permissions->map(fn (Permission $permission): string => str($permission->name)->afterLast(':')->value())
                ->unique()
                ->values()
                ->all(),
        );

        $this->assertSame('finance_officer', AdminPanelProvider::roleBadge(OfferingResource::class));
        $this->assertSame('finance_officer', AdminPanelProvider::roleBadge(PersonalOfferingResource::class));
    }

    /**
     * Either half stands on its own: a resource with a role but nothing
     * to count shows the tag alone, and the two screens everybody
     * shares show neither.
     *
     * 대시보드 and 위키 are the only bare lines left, and deliberately
     * so: a tag answers "who is this menu for", and for those two the
     * answer is everybody who can sign in, which is worth no room.
     */
    public function test_each_half_of_the_badge_degrades_on_its_own(): void
    {
        Filament::setCurrentPanel('admin');
        $this->actingAs($this->superAdmin());

        [$users] = AdminPanelProvider::accessibleItems(UserResource::class);

        $this->assertNull($users->getBadge());
        $this->assertStringContainsString("--role-badge:'관리자'", $users->getExtraAttributeBag()->get('style'));

        foreach ([Dashboard::class, Wiki::class] as $shared) {
            [$item] = AdminPanelProvider::accessibleItems($shared);

            $this->assertNull(AdminPanelProvider::roleBadge($shared), $shared.' grew a tag.');
            $this->assertNull($item->getExtraAttributeBag()->get('style'));
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
