<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Two privileged actions that used to leave no trace: setting somebody
 * else's password, and minting a password reset link for an account
 * that is not your own. Both are legitimate, so the guard is not to
 * block them but to make them visible - and, for the reset link, to
 * keep it away from the accounts it would hand the whole system to.
 */
class PrivilegedActionAuditTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the application roles.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    /**
     * Setting a password on somebody else's account is recorded with
     * the actor and the target, and without the password.
     */
    public function test_setting_a_peer_password_is_recorded(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $peer = User::factory()->create();
        $peer->assignRole('content_editor');

        $this->actingAs($admin);

        $peer->update(['password' => 'a-new-password']);

        $entry = Activity::query()->where('event', 'password_changed')->sole();

        $this->assertSame($admin->id, $entry->causer_id);
        $this->assertSame($peer->id, $entry->subject_id);
        $this->assertSame(User::class, $entry->subject_type);
        $this->assertStringNotContainsString('a-new-password', $entry->toJson());
        $this->assertStringNotContainsString('$2y$', $entry->toJson());
    }

    /**
     * Changing your own password is ordinary account maintenance and
     * is not recorded as somebody reaching into another account.
     */
    public function test_changing_your_own_password_is_not_recorded_as_a_peer_change(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user);

        $user->update(['password' => 'my-own-new-password']);

        $this->assertSame(0, Activity::query()->where('event', 'password_changed')->count());
    }

    /**
     * The roster form's 사이트 계정 section is the path the audit found,
     * so it is worth asserting end to end rather than only on the model.
     */
    public function test_a_password_set_through_the_member_form_is_recorded(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $peer = User::factory()->create(['password' => Hash::make('original')]);
        $peer->assignRole('content_editor');

        Member::create(['name' => '김철수', 'user_id' => $peer->id]);

        $this->actingAs($admin);

        $peer->password = Hash::make('chosen-by-the-admin');
        $peer->save();

        $this->assertSame(1, Activity::query()->where('event', 'password_changed')->count());
    }

    /**
     * Minting a reset link names the developer who asked and the
     * account it opens, and never the token.
     */
    public function test_minting_a_reset_link_is_recorded_without_the_token(): void
    {
        $developer = User::factory()->create();
        $developer->assignRole('developer');

        $target = User::factory()->create();
        $target->assignRole('member');

        $this->actingAs($developer);

        $url = UsersTable::passwordResetUrl($target);

        $entry = Activity::query()->where('event', 'password_reset_link')->sole();

        $this->assertSame($developer->id, $entry->causer_id);
        $this->assertSame($target->id, $entry->subject_id);
        $this->assertStringNotContainsString('token', $entry->toJson());
        $this->assertStringNotContainsString(
            (string) parse_url($url, PHP_URL_QUERY),
            $entry->toJson(),
        );
    }

    /**
     * No link may be minted for a super admin or a fellow developer:
     * the link is a working key, and for those two roles it is a key
     * to everything.
     */
    public function test_no_reset_link_may_be_minted_for_a_super_admin_or_a_developer(): void
    {
        $developer = User::factory()->create();
        $developer->assignRole('developer');

        $this->actingAs($developer);

        foreach (['super_admin', 'developer'] as $role) {
            $target = User::factory()->create();
            $target->assignRole($role);

            $this->assertFalse(UsersTable::mayMintLinkFor($target));

            try {
                UsersTable::passwordResetUrl($target);
                $this->fail("a reset link was minted for a {$role}");
            } catch (HttpException $exception) {
                $this->assertSame(403, $exception->getStatusCode());
            }
        }

        $this->assertSame(0, Activity::query()->where('event', 'password_reset_link')->count());
    }

    /**
     * The specialist staff roles the action was written for are still
     * reachable.
     */
    public function test_a_link_may_still_be_minted_for_ordinary_staff_and_members(): void
    {
        foreach (['member', 'content_editor', 'finance_officer', 'admin'] as $role) {
            $target = User::factory()->create();
            $target->assignRole($role);

            $this->assertTrue(UsersTable::mayMintLinkFor($target));
        }
    }
}
