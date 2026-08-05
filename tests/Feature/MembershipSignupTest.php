<?php

namespace Tests\Feature;

use App\Filament\Resources\MembershipRequests\MembershipRequestResource;
use App\Filament\Resources\MembershipRequests\Pages\ViewMembershipRequest;
use App\Models\Member;
use App\Models\MembershipRequest;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Covers the public sign-up request (가입 신청) and the administrator
 * review that turns an approved request into a working login.
 */
class MembershipSignupTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A valid submission is stored with the password hashed, and the
     * visitor is shown the confirmation screen rather than signed in.
     */
    public function test_the_signup_form_stores_a_request_with_a_hashed_password(): void
    {
        $this->get('/signup')
            ->assertOk()
            ->assertSee('가입 신청하기')
            ->assertSee('남기실 말씀');

        $this->post('/signup', $this->payload())
            ->assertRedirect(route('signup'));

        $request = MembershipRequest::query()->sole();

        $this->assertSame('김철수', $request->name);
        $this->assertSame('대기', $request->status);
        $this->assertNotSame('correct-horse-battery', $request->password);
        $this->assertTrue(Hash::check('correct-horse-battery', $request->password));
        $this->assertGuest();

        $this->followingRedirects()
            ->post('/signup', $this->payload(['email' => 'other@example.com']))
            ->assertOk()
            ->assertSee('가입 신청이 접수되었습니다');
    }

    /**
     * An address that already has a login, or a request still awaiting
     * review, gets the same confirmation screen as anyone else: the
     * form never confirms who is already known to the church.
     */
    public function test_a_duplicate_email_does_not_reveal_that_it_exists(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->followingRedirects()
            ->post('/signup', $this->payload(['email' => 'taken@example.com']))
            ->assertOk()
            ->assertSee('가입 신청이 접수되었습니다')
            ->assertDontSee('이미');

        $this->assertSame(0, MembershipRequest::query()->count());

        /** A second submission for a pending address is dropped just as quietly. */
        $this->post('/signup', $this->payload());
        $this->post('/signup', $this->payload());

        $this->assertSame(1, MembershipRequest::query()->where('email', 'kim@example.com')->count());
    }

    /**
     * Matching ranks name + birth date above name alone above phone.
     */
    public function test_candidates_are_ranked_by_how_strongly_they_match(): void
    {
        $exact = Member::factory()->create(['name' => '김철수', 'birth_date' => '1980-03-02']);
        $sameName = Member::factory()->create(['name' => '김철수', 'birth_date' => '1991-11-11']);
        $samePhone = Member::factory()->create(['name' => '박영수', 'phone' => '0411222333']);
        Member::factory()->create(['name' => '이몽룡', 'phone' => '0400000000']);

        $request = MembershipRequest::create($this->payload());

        $this->assertSame(
            [$exact->id, $sameName->id, $samePhone->id],
            $request->candidates()->pluck('member.id')->all(),
        );
        $this->assertSame('이름 + 생년월일 일치', $request->candidates()->first()['reason']);
    }

    /**
     * 승인 links the chosen roster record, creates the login with the
     * submitted password and gives it the least-privileged role.
     */
    public function test_approval_links_an_existing_member_and_creates_a_working_login(): void
    {
        $member = Member::factory()->create(['name' => '김철수', 'birth_date' => '1980-03-02']);
        $request = MembershipRequest::create($this->payload());

        $request->approve($member, $this->reviewer());

        $user = $member->fresh()->user;

        $this->assertNotNull($user);
        $this->assertSame('kim@example.com', $user->email);
        $this->assertTrue($user->hasRole('member'));
        $this->assertTrue(Hash::check('correct-horse-battery', $user->password));
        $this->assertSame('승인', $request->fresh()->status);
        $this->assertSame($member->id, $request->fresh()->matched_member_id);
        $this->assertNotNull($request->fresh()->reviewed_at);

        $this->assertTrue(Auth::attempt(['email' => 'kim@example.com', 'password' => 'correct-horse-battery']));
        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));
    }

    /**
     * 승인 without a candidate registers a fresh, unpublished 성도.
     */
    public function test_approval_without_a_candidate_registers_a_new_member(): void
    {
        $request = MembershipRequest::create($this->payload());

        $request->approve(null, $this->reviewer());

        $member = Member::query()->where('name', '김철수')->sole();

        $this->assertFalse($member->is_published);
        $this->assertSame('0411222333', $member->phone);
        $this->assertNotNull($member->user_id);
    }

    /**
     * 거절 records the review and creates nothing at all.
     */
    public function test_rejection_creates_no_user(): void
    {
        $request = MembershipRequest::create($this->payload());
        $reviewer = $this->reviewer();

        $request->reject($reviewer);

        $this->assertSame('거절', $request->fresh()->status);
        $this->assertSame($reviewer->id, $request->fresh()->reviewed_by);
        $this->assertSame(0, User::query()->where('email', 'kim@example.com')->count());
        $this->assertSame(0, Member::query()->where('name', '김철수')->count());
    }

    /**
     * The review pages are admin-only; guests never reach them.
     */
    public function test_a_guest_cannot_reach_the_review_resource(): void
    {
        $request = MembershipRequest::create($this->payload());

        $this->get(MembershipRequestResource::getUrl('index'))->assertRedirect();
        $this->get(MembershipRequestResource::getUrl('view', ['record' => $request]))->assertRedirect();
        $this->assertGuest();
    }

    /**
     * An approved 성도 holds the permissionless 'member' role: the
     * panel still lets them in (the dashboard renders, so they never
     * meet an error page) but every resource is refused.
     */
    public function test_an_approved_member_reaches_the_dashboard_but_no_resource(): void
    {
        $request = MembershipRequest::create($this->payload());
        $user = $request->approve(null, $this->reviewer());

        $this->actingAs($user)->get('/admin')->assertOk();
        $this->actingAs($user)->get(MembershipRequestResource::getUrl('index'))->assertForbidden();
        $this->actingAs($user)->get('/admin/members')->assertForbidden();
    }

    /**
     * The review page renders with its candidate list and the two
     * review actions available to an authorised administrator.
     */
    public function test_the_review_page_renders_with_its_actions(): void
    {
        Member::factory()->create(['name' => '김철수', 'birth_date' => '1980-03-02']);
        $request = MembershipRequest::create($this->payload());

        Livewire::actingAs($this->reviewer())
            ->test(ViewMembershipRequest::class, ['record' => $request->getKey()])
            ->assertSuccessful()
            ->assertActionVisible('approve')
            ->assertActionVisible('reject');
    }

    /**
     * A valid form submission.
     *
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => '김철수',
            'birth_date' => '1980-03-02',
            'phone' => '0411222333',
            'email' => 'kim@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
            'note' => '주일 2부 예배에 출석합니다.',
        ], $overrides);
    }

    /**
     * An administrator authorised to review requests.
     */
    private function reviewer(): User
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('super_admin');

        foreach (['ViewAny:MembershipRequest', 'View:MembershipRequest', 'Update:MembershipRequest'] as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            $user->givePermissionTo($permission);
        }

        return $user;
    }
}
