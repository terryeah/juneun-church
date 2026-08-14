<?php

namespace Tests\Feature;

use App\Filament\Resources\Members\Pages\CreateMember;
use App\Filament\Resources\Members\Pages\EditMember;
use App\Models\Member;
use App\Models\MembershipRequest;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Covers the 사이트 계정 section of the member form: creating a login,
 * editing it, and deleting it when the toggle is switched off.
 */
class MemberSiteAccountTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The administrator driving the member form.
     */
    private User $admin;

    /**
     * Seed the roles and the Member permissions, then sign in.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');

        foreach (['ViewAny:Member', 'View:Member', 'Create:Member', 'Update:Member', 'Delete:Member'] as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            $this->admin->givePermissionTo($permission);
        }
    }

    /**
     * The owner's sequence: a 가입 신청 is approved onto an existing
     * roster record, the administrator opens that record and switches
     * 사이트 계정 off. The save asks first, and once confirmed the login
     * is gone for good - reopening the page shows the toggle off.
     */
    public function test_switching_the_site_account_toggle_off_deletes_the_login(): void
    {
        $member = Member::factory()->create(['name' => '김철수', 'birth_date' => '1980-03-02']);
        $request = MembershipRequest::create($this->signupPayload());
        $user = $request->approve($member, $this->admin, '전화 통화로 확인');

        $this->assertSame($user->id, $member->fresh()->user_id);

        $page = Livewire::actingAs($this->admin)
            ->test(EditMember::class, ['record' => $member->getKey()])
            ->assertFormSet(['site_account' => true])
            ->fillForm(['site_account' => false])
            ->call('save')
            ->assertActionMounted('confirmSiteAccountRevocation');

        /** The question names the account and what deleting it costs. */
        $modal = $page->instance()->getMountedAction();

        $this->assertSame('로그인 계정을 삭제할까요?', $modal->getModalHeading());
        $this->assertStringContainsString('kim@example.com', $modal->getModalDescription());

        /** Nothing is destroyed while the question is still on screen. */
        $this->assertSame($user->id, $member->fresh()->user_id);
        $this->assertNotNull(User::query()->find($user->id));

        $page->callMountedAction()->assertHasNoFormErrors();

        $this->assertNull($member->fresh()->user_id);
        $this->assertNull(User::query()->find($user->id));
        $this->assertFalse(Auth::attempt(['email' => 'kim@example.com', 'password' => 'correct-horse-battery']));

        /** The approved request still points at the roster record, not at a dead account. */
        $this->assertSame('승인', $request->fresh()->status);
        $this->assertSame($member->id, $request->fresh()->matched_member_id);

        /** The role assignment goes with the account rather than outliving it. */
        $this->assertSame(0, DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->where('model_id', $user->id)
            ->count());

        Livewire::actingAs($this->admin)
            ->test(EditMember::class, ['record' => $member->getKey()])
            ->assertFormSet(['site_account' => false]);
    }

    /**
     * An administrator cannot delete the account they are signed in
     * with: the save is refused with a message rather than confirmed.
     */
    public function test_an_administrator_cannot_delete_their_own_login(): void
    {
        $member = Member::factory()->create(['user_id' => $this->admin->getKey()]);

        Livewire::actingAs($this->admin)
            ->test(EditMember::class, ['record' => $member->getKey()])
            ->fillForm(['site_account' => false])
            ->call('save')
            ->assertActionNotMounted()
            ->assertNotified('본인 계정은 삭제할 수 없습니다');

        $this->assertSame($this->admin->id, $member->fresh()->user_id);
        $this->assertNotNull(User::query()->find($this->admin->id));
    }

    /**
     * Leaving the toggle on still creates a login on the create page
     * and updates the email, password and roles on the edit page,
     * without ever raising the confirmation.
     */
    public function test_the_toggle_on_state_still_creates_and_updates_a_login(): void
    {
        $editorRole = Role::query()->where('name', 'content_editor')->sole();

        Livewire::actingAs($this->admin)
            ->test(CreateMember::class)
            ->fillForm([
                'name' => '박영수',
                'status' => '재적',
                'site_account' => true,
                'site_email' => 'park@example.com',
                'site_password' => 'correct-horse-battery',
                'site_roles' => $editorRole->id,
            ])
            ->call('create')
            ->assertActionNotMounted()
            ->assertHasNoFormErrors();

        $member = Member::query()->where('name', '박영수')->sole();
        $user = $member->user;

        $this->assertNotNull($user);
        $this->assertSame('park@example.com', $user->email);
        $this->assertTrue($user->hasRole('content_editor'));
        $this->assertTrue(Hash::check('correct-horse-battery', $user->password));

        Livewire::actingAs($this->admin)
            ->test(EditMember::class, ['record' => $member->getKey()])
            ->fillForm([
                'site_email' => 'park.new@example.com',
                'site_password' => 'a-brand-new-secret',
                'site_roles' => Role::query()->where('name', 'admin')->sole()->id,
            ])
            ->call('save')
            ->assertActionNotMounted()
            ->assertHasNoFormErrors();

        $user->refresh();

        $this->assertSame($user->id, $member->fresh()->user_id);
        $this->assertSame('park.new@example.com', $user->email);
        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('content_editor'));

        /** The field takes one role, so switching replaces rather than adds. */
        $this->assertSame(1, $user->roles()->count());
        $this->assertTrue(Hash::check('a-brand-new-secret', $user->password));
    }

    /**
     * A roster record that never had a login saves as it always did:
     * the toggle is off, and there is nothing to ask about.
     */
    public function test_a_member_without_a_login_saves_without_confirmation(): void
    {
        $member = Member::factory()->create();

        Livewire::actingAs($this->admin)
            ->test(EditMember::class, ['record' => $member->getKey()])
            ->fillForm(['name' => '이름 수정'])
            ->call('save')
            ->assertActionNotMounted()
            ->assertHasNoFormErrors();

        $this->assertSame('이름 수정', $member->fresh()->name);
        $this->assertNull($member->fresh()->user_id);
    }

    /**
     * A valid 가입 신청 submission.
     *
     * @return array<string, string>
     */
    private function signupPayload(): array
    {
        return [
            'name' => '김철수',
            'birth_date' => '1980-03-02',
            'phone' => '0411222333',
            'email' => 'kim@example.com',
            'password' => 'correct-horse-battery',
        ];
    }
}
