<?php

namespace Tests\Feature;

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\MembershipRequests\MembershipRequestResource;
use App\Filament\Resources\MembershipRequests\Pages\ViewMembershipRequest;
use App\Models\Member;
use App\Models\MembershipRequest;
use App\Models\User;
use App\Notifications\MembershipApproved;
use App\Notifications\MembershipRequested;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
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
            ->assertSee('가입 신청이 완료되었습니다');
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
            ->assertSee('가입 신청이 완료되었습니다')
            ->assertDontSee('이미');

        $this->assertSame(0, MembershipRequest::query()->count());

        /** A second submission for a pending address is dropped just as quietly. */
        $this->post('/signup', $this->payload());
        $this->post('/signup', $this->payload());

        $this->assertSame(1, MembershipRequest::query()->where('email', 'kim@example.com')->count());
    }

    /**
     * A dropped submission must also cost the same time as a stored
     * one, or the silence is undone by the clock.
     *
     * Hashing dominates this request. When only the stored path paid
     * for it, an address already known to the church answered in about
     * a millisecond while an unknown address took the better part of a
     * second, which anyone could measure from anywhere. The two paths
     * are compared here at the production hashing cost; the margin is
     * wide because the assertion has to survive a loaded machine, but
     * the regression it guards against was a factor of two hundred.
     */
    public function test_a_dropped_duplicate_costs_the_same_time_as_a_stored_request(): void
    {
        config(['hashing.bcrypt.rounds' => 12]);
        $this->withoutMiddleware(ThrottleRequests::class);

        User::factory()->create(['email' => 'taken@example.com']);

        $stored = $this->timeSignup(['email' => 'fresh@example.com']);
        $dropped = $this->timeSignup(['email' => 'taken@example.com']);

        $this->assertSame(1, MembershipRequest::query()->count());
        $this->assertGreaterThan(
            $stored * 0.5,
            $dropped,
            "A dropped submission answered in {$dropped}ms against {$stored}ms for a stored one, "
                .'which tells a stranger whether the address belongs to the church.',
        );
    }

    /**
     * Milliseconds spent answering one sign-up submission.
     *
     * @param  array<string, string>  $overrides
     */
    private function timeSignup(array $overrides): float
    {
        $start = microtime(true);

        $this->post('/signup', $this->payload($overrides));

        return (microtime(true) - $start) * 1000;
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

        $request->approve($member, $this->reviewer(), '전화 통화로 확인', '8월 6일 셀장 확인');

        $user = $member->fresh()->user;

        $this->assertNotNull($user);
        $this->assertSame('kim@example.com', $user->email);
        $this->assertTrue($user->hasRole('general_member'));
        $this->assertTrue(Hash::check('correct-horse-battery', $user->password));
        $this->assertSame('승인', $request->fresh()->status);
        $this->assertSame($member->id, $request->fresh()->matched_member_id);
        $this->assertNotNull($request->fresh()->reviewed_at);
        $this->assertSame('전화 통화로 확인', $request->fresh()->verification_method);
        $this->assertSame('8월 6일 셀장 확인', $request->fresh()->verification_note);

        $this->assertTrue(Auth::attempt(['email' => 'kim@example.com', 'password' => 'correct-horse-battery']));
        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));
    }

    /**
     * 승인 without a candidate registers a fresh, unpublished 성도.
     */
    public function test_approval_without_a_candidate_registers_a_new_member(): void
    {
        $request = MembershipRequest::create($this->payload());

        $request->approve(null, $this->reviewer(), '직접 만나 확인');

        $member = Member::query()->where('name', '김철수')->sole();

        $this->assertFalse($member->is_published);
        $this->assertSame('0411222333', $member->phone);
        $this->assertNotNull($member->user_id);
    }

    /**
     * The comparison says exactly where each submitted value came from:
     * a phone number the church recorded itself corroborates, a value
     * the roster does not hold is only the applicant's own word, and a
     * value the roster contradicts is a warning.
     */
    public function test_the_comparison_reports_each_field_against_the_roster_record(): void
    {
        $request = MembershipRequest::create($this->payload());

        $onFile = Member::factory()->create([
            'name' => '김철수',
            'birth_date' => '1980-03-02',
            'phone' => '0411222333',
        ]);

        $verdicts = collect($request->comparison($onFile))->pluck('verdict', 'field');

        $this->assertSame('일치', $verdicts['이름']);
        $this->assertSame('일치', $verdicts['생년월일']);
        $this->assertSame('일치', $verdicts['전화번호']);
        $this->assertSame('자기 신고', $verdicts['이메일']);
        $this->assertSame('교회 기록 전화번호 일치', $request->corroboration($onFile));

        /** A roster record holding a different number contradicts the applicant. */
        $conflicting = Member::factory()->create(['name' => '김철수', 'phone' => '0400999888']);

        $this->assertSame('불일치', collect($request->comparison($conflicting))->pluck('verdict', 'field')['전화번호']);
        $this->assertSame('불일치 항목 있음', $request->corroboration($conflicting));

        /** With nothing on file, a name and birth date agreeing prove nothing. */
        $bare = Member::factory()->create(['name' => '김철수', 'birth_date' => '1980-03-02']);

        $this->assertSame(
            ['일치', '일치', '자기 신고', '자기 신고'],
            collect($request->comparison($bare))->pluck('verdict')->all(),
        );
        $this->assertSame('신고 내용만 일치 (본인 확인 아님)', $request->corroboration($bare));
    }

    /**
     * 승인 cannot go through on a hunch: the administrator has to say
     * how they confirmed the applicant is the person they name.
     */
    public function test_approval_is_refused_without_a_verification_method(): void
    {
        $member = Member::factory()->create(['name' => '김철수', 'birth_date' => '1980-03-02']);
        $request = MembershipRequest::create($this->payload());

        Livewire::actingAs($this->reviewer())
            ->test(ViewMembershipRequest::class, ['record' => $request->getKey()])
            ->callAction('approve', ['member_id' => $member->getKey()])
            ->assertHasActionErrors(['verification_method' => 'required']);

        $this->assertSame('대기', $request->fresh()->status);
        $this->assertSame(0, User::query()->where('email', 'kim@example.com')->count());

        /** 기타 says nothing on its own, so it has to be written out. */
        Livewire::actingAs($this->reviewer())
            ->test(ViewMembershipRequest::class, ['record' => $request->getKey()])
            ->callAction('approve', ['verification_method' => '기타'])
            ->assertHasActionErrors(['verification_note' => 'required']);

        $this->assertSame('대기', $request->fresh()->status);
    }

    /**
     * A completed 승인 stores how identity was confirmed, on the new
     * member path as much as the linking one, and the activity log
     * keeps the decision without ever touching the password.
     */
    public function test_the_approval_action_records_how_identity_was_confirmed(): void
    {
        $request = MembershipRequest::create($this->payload());

        Livewire::actingAs($this->reviewer())
            ->test(ViewMembershipRequest::class, ['record' => $request->getKey()])
            ->callAction('approve', [
                'verification_method' => '가족 또는 셀장·교역자가 확인',
                'verification_note' => '셀장 박영수가 8월 6일 확인함.',
            ])
            ->assertHasNoActionErrors();

        $request->refresh();

        $this->assertSame('승인', $request->status);
        $this->assertSame('가족 또는 셀장·교역자가 확인', $request->verification_method);
        $this->assertSame('셀장 박영수가 8월 6일 확인함.', $request->verification_note);

        /** The columns are review-only: a public submission can never set them. */
        $this->assertSame([], array_intersect(
            ['verification_method', 'verification_note'],
            $request->getFillable(),
        ));

        $logged = Activity::query()
            ->where('subject_type', MembershipRequest::class)
            ->where('event', 'updated')
            ->latest('id')
            ->first()
            ->attribute_changes['attributes'];

        $this->assertSame('가족 또는 셀장·교역자가 확인', $logged['verification_method']);
        $this->assertSame($request->matched_member_id, $logged['matched_member_id']);
        $this->assertNotNull($logged['reviewed_by']);
        $this->assertArrayNotHasKey('password', $logged);
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
     * An approved 성도 holds the permissionless 'general_member' role: the
     * panel still lets them in, but only as far as their own profile.
     * The dashboard and every resource - which the role could never
     * open anyway - now redirect there instead of refusing outright,
     * so a 성도 never meets an error page.
     */
    public function test_an_approved_member_reaches_only_their_profile(): void
    {
        $request = MembershipRequest::create($this->payload());
        $user = $request->approve(null, $this->reviewer(), '직접 만나 확인');
        $profileUrl = Filament::getPanel('admin')->getProfileUrl();

        $this->actingAs($user);
        $this->assertFalse(Dashboard::canAccess());

        $this->actingAs($user)->get('/admin')->assertRedirect($profileUrl);
        $this->actingAs($user)->get(MembershipRequestResource::getUrl('index'))->assertRedirect($profileUrl);
        $this->actingAs($user)->get('/admin/members')->assertRedirect($profileUrl);
        $this->actingAs($user)->get($profileUrl)->assertOk();
    }

    /**
     * The review page renders the comparison table, which labels every
     * submitted field, and both review actions.
     */
    public function test_the_review_page_renders_with_its_actions(): void
    {
        Member::factory()->create(['name' => '김철수', 'birth_date' => '1980-03-02', 'phone' => '0411222333']);
        $request = MembershipRequest::create($this->payload());

        Livewire::actingAs($this->reviewer())
            ->test(ViewMembershipRequest::class, ['record' => $request->getKey()])
            ->assertSuccessful()
            ->assertSee('교적부 대조')
            ->assertSee('자기 신고')
            ->assertSee('일치')
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
     * A request that nobody knows about is a request nobody reviews.
     *
     * The office is written to at an address on the church's own domain,
     * so who actually reads it is a mail routing question rather than a
     * deploy - and the mail carries the applicant's name only, because
     * an inbox is the wrong place to copy somebody's birth date to.
     */
    public function test_a_new_request_tells_the_office(): void
    {
        NotificationFacade::fake();
        Carbon::setTestNow('2026-08-14 14:05:00');

        $this->post('/signup', $this->payload());

        NotificationFacade::assertSentOnDemand(MembershipRequested::class, function (MembershipRequested $notification, array $channels, object $notifiable): bool {
            $this->assertSame([config('mail.office.address')], array_keys($notifiable->routes['mail']));

            $mail = $notification->toMail($notifiable);
            $body = implode(' ', $mail->introLines);

            $this->assertStringContainsString('김철수', $mail->subject);

            /** Written the way a person says it, not the way a clock reads. */
            $this->assertStringContainsString('2026년 8월 14일, 오후 2시 5분에 신청하셨습니다', $body);

            $this->assertStringNotContainsString('1980-03-02', $body);
            $this->assertStringNotContainsString('kim@example.com', $body);

            return true;
        });
    }

    /**
     * On the hour the minutes go altogether: '오후 8시 00분' is a clock,
     * not a sentence.
     */
    public function test_the_office_notice_drops_the_minutes_on_the_hour(): void
    {
        Carbon::setTestNow('2026-08-14 20:00:00');

        $mail = (new MembershipRequested(MembershipRequest::create($this->payload())))
            ->toMail(new AnonymousNotifiable);

        $this->assertStringContainsString('2026년 8월 14일, 오후 8시에 신청하셨습니다', implode(' ', $mail->introLines));
    }

    /**
     * A silently dropped duplicate writes to nobody: there is no request
     * to review, and the mail would put the very fact the silence exists
     * to keep - that this address is already known to the church - into
     * an inbox.
     */
    public function test_a_dropped_duplicate_tells_nobody(): void
    {
        NotificationFacade::fake();

        User::factory()->create(['email' => 'taken@example.com']);

        $this->post('/signup', $this->payload(['email' => 'taken@example.com']));

        NotificationFacade::assertNothingSent();
    }

    /**
     * An approved applicant is told, at the address they applied with.
     *
     * Somebody who applied and heard nothing has no way to know it
     * worked, and the one thing the mail has to carry is that the
     * password is the one they chose - otherwise they write to the
     * office asking where it is.
     */
    public function test_an_approval_tells_the_applicant(): void
    {
        NotificationFacade::fake();

        $request = MembershipRequest::create($this->payload());
        $user = $request->approve(null, $this->reviewer(), '직접 만나 확인');

        NotificationFacade::assertSentTo($user, MembershipApproved::class, function (MembershipApproved $notification) use ($user): bool {
            $mail = $notification->toMail($user);

            $this->assertStringContainsString('가입 신청하실 때 직접 정하신 비밀번호', implode(' ', $mail->introLines));
            $this->assertSame(route('login'), $mail->actionUrl);

            /** no-reply@ is a routing rule, not a mailbox, so a reply has somewhere to land. */
            $this->assertSame(config('mail.reply_to.address'), $mail->replyTo[0][0]);

            return true;
        });
    }

    /**
     * The office can approve without writing, for somebody they have
     * already rung.
     */
    public function test_the_approval_mail_can_be_withheld(): void
    {
        NotificationFacade::fake();

        MembershipRequest::create($this->payload())
            ->approve(null, $this->reviewer(), '전화 통화로 확인', notify: false);

        NotificationFacade::assertNothingSent();
    }

    /**
     * An account alone does not open 성도 전용 content - the 교적 record
     * does - so the mail may not promise it to somebody left off the
     * roster.
     */
    public function test_the_mail_only_promises_성도_전용_to_somebody_on_the_roster(): void
    {
        $reviewer = $this->reviewer();
        $user = User::factory()->create();

        $onRoster = (new MembershipApproved(true))->toMail($user);
        $accountOnly = (new MembershipApproved(false))->toMail($user);

        $this->assertStringContainsString('성도 전용 자료와 헌금 내역도 함께', implode(' ', $onRoster->outroLines));
        $this->assertStringContainsString('교적에 등록된 뒤에', implode(' ', $accountOnly->outroLines));

        /** And the flag follows the outcome the administrator picked. */
        $request = MembershipRequest::create($this->payload());
        $request->approve(null, $reviewer, '직접 만나 확인', registerOnRoster: false, notify: false);

        $this->assertNull($request->fresh()->matched_member_id);
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
