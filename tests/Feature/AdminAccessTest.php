<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\Cells\CellResource;
use App\Filament\Resources\Members\MemberResource;
use App\Filament\Resources\MembershipRequests\MembershipRequestResource;
use App\Filament\Resources\Offerings\OfferingResource;
use App\Filament\Resources\PersonalOfferings\PersonalOfferingResource;
use App\Filament\Resources\Positions\PositionResource;
use App\Filament\Resources\SiteSettings\SiteSettingResource;
use App\Filament\Resources\StaffMembers\StaffMemberResource;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Widgets\ContentStatsWidget;
use App\Http\Middleware\ExemptMembersFromMultiFactorAuthentication;
use App\Models\MembershipRequest;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * Covers what an ordinary 성도 may and may not reach in the admin
 * panel: no mandatory two-factor prompt, no dashboard widgets, no
 * resources - and the developer's way of helping one of them back in.
 */
class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Mandatory two-factor authentication skips a member-only account
     * and still catches everyone else.
     *
     * The check runs against the middleware rather than a request
     * because Filament decides whether to attach it while registering
     * routes, and the panel switches the requirement off under tests.
     */
    public function test_mandatory_two_factor_authentication_exempts_members_only(): void
    {
        /**
         * Filament only registers the set-up page when the requirement
         * is on, which it is not under tests, so the redirect target
         * is stood in for. Naming the route up front keeps it in the
         * router's name lookup, which is built as routes are added.
         */
        Route::name('filament.admin.auth.multi-factor-authentication.set-up-required')
            ->get('/admin/2fa-set-up', fn (): string => 'set up');

        $middleware = new ExemptMembersFromMultiFactorAuthentication;
        $next = fn (): Response => new Response('reached');

        $this->actingAs($this->userWithRoles(['general_member']));
        $this->assertSame('reached', $middleware->handle(Request::create('/admin/profile'), $next)->getContent());

        $this->actingAs($this->userWithRoles(['admin']));
        $response = $middleware->handle(Request::create('/admin'), $next);

        $this->assertTrue($response->isRedirect());
        $this->assertStringContainsString('/admin/2fa-set-up', $response->getTargetUrl());

        /** A member who also holds a staff role is staff again. */
        $this->actingAs($this->userWithRoles(['general_member', 'content_editor']));
        $this->assertTrue($middleware->handle(Request::create('/admin'), $next)->isRedirect());

        /** The per-role test accounts are waived whatever role they carry. */
        $tester = $this->userWithRoles(['admin']);
        $tester->forceFill(['is_test_account' => true])->save();
        $this->actingAs($tester);
        $this->assertSame('reached', $middleware->handle(Request::create('/admin'), $next)->getContent());
    }

    /**
     * Every dashboard widget of this application is hidden from anyone
     * below an administrator, and the statistics still show for one.
     */
    public function test_dashboard_widgets_are_hidden_below_an_administrator(): void
    {
        $widgets = array_filter(
            Filament::getPanel('admin')->getWidgets(),
            fn (string $widget): bool => str_starts_with($widget, 'App\\Filament\\Widgets\\'),
        );

        $this->assertNotEmpty($widgets);

        foreach ([['general_member'], ['content_editor']] as $roles) {
            $this->actingAs($this->userWithRoles($roles));

            foreach ($widgets as $widget) {
                $this->assertFalse($widget::canView(), "{$widget} is visible to a ".implode(', ', $roles).'.');
            }
        }

        $this->actingAs($this->userWithRoles(['admin']));
        $this->assertTrue(ContentStatsWidget::canView());
    }

    /**
     * 재정 담당 signs in like any other staff account: the middleware
     * only diverts member-only accounts, and the dashboard is closed
     * only to those, so a finance officer lands on 대시보드 with the
     * account card alone - the same bare dashboard a content editor
     * already gets - and finds 헌금 내역 and 개인 헌금 in the sidebar.
     * Nothing holding the congregation's personal details opens.
     */
    public function test_a_finance_officer_reaches_the_offerings_and_nothing_else(): void
    {
        $this->actingAs($this->administrator(['finance_officer']));

        $this->get('/admin')->assertOk();
        $this->assertTrue(Dashboard::canAccess());

        $this->assertTrue(OfferingResource::canAccess());
        $this->assertTrue(PersonalOfferingResource::canAccess());

        foreach ([
            MemberResource::class,
            CellResource::class,
            UserResource::class,
            StaffMemberResource::class,
            MembershipRequestResource::class,
            SiteSettingResource::class,
            PositionResource::class,
        ] as $resource) {
            $this->assertFalse($resource::canAccess(), $resource.' opened to a finance officer.');
        }
    }

    /**
     * The short public address hands a 성도 straight to the panel's
     * profile page, and staff are not diverted anywhere.
     */
    public function test_the_profile_route_points_at_the_panel_profile(): void
    {
        $profileUrl = Filament::getPanel('admin')->getProfileUrl();

        $this->get('/profile')->assertRedirect($profileUrl);

        $this->actingAs($this->administrator())->get('/admin')->assertOk();
    }

    /**
     * The light / dark / system switcher reaches the login screen,
     * where Filament otherwise hides it inside the user menu.
     */
    public function test_the_login_page_carries_the_theme_switcher(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('fi-login-theme-switcher')
            ->assertSee('fi-theme-switcher-btn');
    }

    /**
     * The reset-link action belongs to the developer alone: a super
     * admin without that role never sees it.
     */
    public function test_the_password_reset_link_action_is_developer_only(): void
    {
        $target = $this->userWithRoles(['general_member']);

        Livewire::actingAs($this->administrator(['super_admin']))
            ->test(ListUsers::class)
            ->assertTableActionHidden('passwordResetLink', $target);

        Livewire::actingAs($this->administrator(['super_admin', 'developer']))
            ->test(ListUsers::class)
            ->assertTableActionVisible('passwordResetLink', $target);
    }

    /**
     * The 가입 경로 column reads the account's origin: an approved
     * 가입 신청 reaches back through the roster record it was matched
     * to, while an account the office registered has nothing to find.
     */
    public function test_an_account_knows_whether_it_came_from_a_signup_request(): void
    {
        $reviewer = $this->administrator(['super_admin']);

        $signedUp = MembershipRequest::create([
            'name' => '김철수',
            'birth_date' => '1980-03-02',
            'phone' => '0411222333',
            'email' => 'kim@example.com',
            'password' => 'correct-horse-battery',
        ])->approve(null, $reviewer, '전화 통화로 확인');

        $this->assertNotNull($signedUp->membershipRequest);
        $this->assertNull($reviewer->membershipRequest);
    }

    /**
     * The generated link is a genuine, signed Laravel reset link: the
     * token is recorded against the account and the page opens.
     */
    public function test_the_generated_password_reset_link_validates(): void
    {
        $user = $this->userWithRoles(['general_member']);

        $url = UsersTable::passwordResetUrl($user);
        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        $this->assertTrue(Password::broker()->tokenExists($user, $query['token']));
        $this->assertSame($user->email, $query['email']);

        $this->get($url)->assertOk();
    }

    /**
     * An account holding exactly the given roles.
     *
     * @param  list<string>  $roles
     */
    private function userWithRoles(array $roles): User
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->syncRoles($roles);

        return $user;
    }

    /**
     * An administrator with every resource permission, so the pages
     * under test render rather than refusing.
     *
     * @param  list<string>  $roles
     */
    private function administrator(array $roles = ['admin']): User
    {
        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            Permission::findOrCreate('ViewAny:'.class_basename($resource::getModel()), 'web');
        }

        $this->seed(RoleSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        return $this->userWithRoles($roles);
    }
}
