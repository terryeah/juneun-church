<?php

namespace Tests\Feature;

use App\Filament\Resources\Members\Pages\EditMember;
use App\Models\Member;
use App\Models\MembershipRequest;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Covers the 가입 신청 내용 section of the member form: reading back
 * what an applicant typed into the public sign-up form, for a roster
 * record whose account came from one.
 */
class MemberSignupSubmissionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The administrator opening the member form.
     */
    private User $admin;

    /**
     * Seed the roles and sign in an administrator holding both the
     * roster and the sign-up permissions.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->admin = $this->userWith([
            'ViewAny:Member', 'View:Member', 'Update:Member',
            'ViewAny:MembershipRequest', 'View:MembershipRequest', 'Update:MembershipRequest',
        ]);
    }

    /**
     * The owner's case: the applicant's browser autofilled a romanised
     * name, the office approved onto the right 교적 record anyway, and
     * the roster form now shows what was submitted beside what the
     * church holds - with the name marked 불일치.
     */
    public function test_the_form_shows_what_the_applicant_submitted(): void
    {
        $member = Member::factory()->create([
            'name' => '박예지',
            'birth_date' => '1988-05-04',
            'phone' => '0411222333',
        ]);

        $request = MembershipRequest::create([
            'name' => 'Yeji Park',
            'birth_date' => '1988-05-04',
            'phone' => '0411222333',
            'email' => 'yeji@example.com',
            'password' => 'correct-horse-battery',
            'note' => '남편과 함께 등록했습니다.',
        ]);

        $request->approve($member, $this->admin, '전화 통화로 확인');

        $section = $this->signupSection($member);

        $this->assertNotNull($section, '가입 신청 내용 section should be visible.');

        $comparison = $this->entryState($section, 'signup_comparison');

        $this->assertSame(
            [
                ['이름', 'Yeji Park', '박예지', MembershipRequest::VERDICT_CONFLICT],
                ['생년월일', '1988-05-04', '1988-05-04', MembershipRequest::VERDICT_MATCH],
                ['전화번호', '0411222333', '0411222333', MembershipRequest::VERDICT_MATCH],
                ['이메일', 'yeji@example.com', null, MembershipRequest::VERDICT_SELF_DECLARED],
            ],
            array_map(
                fn (array $row): array => [$row['field'], $row['submitted'], $row['held'], $row['verdict']],
                $comparison,
            ),
        );

        $this->assertSame('남편과 함께 등록했습니다.', $this->entryState($section, 'signup_note'));
        $this->assertSame('전화 통화로 확인', $this->entryState($section, 'signup_verification_method'));
        $this->assertSame($this->admin->name, $this->entryState($section, 'signup_reviewer'));
        $this->assertNotNull($this->entryState($section, 'signup_reviewed_at'));

        /** The heading counts the disagreement, so the office sees it without opening the section. */
        $this->assertSame('가입 신청 내용 · 다른 항목 1건', $this->signupSection($member)->getHeading());
    }

    /**
     * Most of the roster was registered by the office and has no
     * sign-up request behind it, so the section stays off the page
     * rather than appearing empty.
     */
    public function test_the_section_is_absent_for_a_member_registered_by_hand(): void
    {
        $member = Member::factory()->create();

        $this->assertNull($this->signupSection($member));
    }

    /**
     * A roster record is edited by more people than review sign-ups, so
     * the section answers to the 가입 신청 permission and not to the
     * roster's own.
     */
    public function test_the_section_is_hidden_without_the_signup_permission(): void
    {
        $member = Member::factory()->create(['name' => '박예지']);

        MembershipRequest::create([
            'name' => '박예지',
            'birth_date' => '1988-05-04',
            'phone' => '0411222333',
            'email' => 'yeji@example.com',
            'password' => 'correct-horse-battery',
        ])->approve($member, $this->admin, '전화 통화로 확인');

        $editor = $this->userWith(['ViewAny:Member', 'View:Member', 'Update:Member']);

        $this->assertNull($this->signupSection($member, $editor));
    }

    /**
     * The read is recorded only when the read actually happened.
     *
     * Opening the form writes an activity row against the 가입 신청 so
     * an auditor filtering on it finds reads made from here. The page
     * asked the permission question for itself at first, and drifted
     * from the section's: a viewer holding View but not ViewAny was
     * shown nothing and still had a 열람 recorded in their name.
     */
    public function test_the_read_is_logged_only_for_a_viewer_who_is_shown_it(): void
    {
        $member = Member::factory()->create(['name' => '박예지']);

        $request = MembershipRequest::create([
            'name' => '박예지',
            'birth_date' => '1988-05-04',
            'phone' => '0411222333',
            'email' => 'yeji@example.com',
            'password' => 'correct-horse-battery',
        ]);

        $request->approve($member, $this->admin, '전화 통화로 확인');

        $reads = fn (User $viewer): int => Activity::query()
            ->where('subject_type', MembershipRequest::class)
            ->where('subject_id', $request->getKey())
            ->where('causer_id', $viewer->getKey())
            ->where('event', 'visited')
            ->count();

        /** Holds View but not ViewAny, so the section stays hidden. */
        $partial = $this->userWith(['ViewAny:Member', 'View:Member', 'Update:Member', 'View:MembershipRequest']);

        $this->assertNull($this->signupSection($member, $partial));
        $this->assertSame(0, $reads($partial));

        $this->assertNotNull($this->signupSection($member, $this->admin));
        $this->assertSame(1, $reads($this->admin));
    }

    /**
     * A member with no sign-up behind them leaves no read trail either.
     */
    public function test_opening_an_ordinary_member_logs_no_signup_read(): void
    {
        $member = Member::factory()->create();

        $this->signupSection($member);

        $this->assertSame(0, Activity::query()
            ->where('subject_type', MembershipRequest::class)
            ->where('event', 'visited')
            ->count());
    }

    /**
     * Once membership:redact has stripped a settled request, the
     * section says the details are gone instead of comparing the roster
     * against a row of '지움'.
     */
    public function test_a_redacted_request_shows_a_notice_rather_than_a_comparison(): void
    {
        $member = Member::factory()->create(['name' => '박예지']);

        $request = MembershipRequest::create([
            'name' => '박예지',
            'birth_date' => '1988-05-04',
            'phone' => '0411222333',
            'email' => 'yeji@example.com',
            'password' => 'correct-horse-battery',
        ]);

        $request->approve($member, $this->admin, '전화 통화로 확인');
        $request->forceFill(['reviewed_at' => now()->subDays(120)])->saveQuietly();

        $this->artisan('membership:redact')->assertSuccessful();

        $section = $this->signupSection($member);

        $this->assertNotNull($section);
        $this->assertTrue($this->redactionNotice($section)?->isVisible());
        $this->assertFalse($this->entry($section, 'signup_comparison')->isVisible());
        $this->assertFalse($this->entry($section, 'signup_note')->isVisible());

        /** The processing record survives the redaction and still reads. */
        $this->assertSame('전화 통화로 확인', $this->entryState($section, 'signup_verification_method'));
    }

    /**
     * An account holding the named permissions and nothing else.
     *
     * @param  list<string>  $permissions
     */
    private function userWith(array $permissions): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            $user->givePermissionTo($permission);
        }

        return $user;
    }

    /**
     * The 가입 신청 내용 section on the member form, or null when it is
     * not shown to this viewer.
     */
    private function signupSection(Member $member, ?User $viewer = null): mixed
    {
        $component = Livewire::actingAs($viewer ?? $this->admin)
            ->test(EditMember::class, ['record' => $member->getKey()]);

        $section = collect($component->instance()->getSchema('form')->getComponents(withHidden: true))
            ->first(fn (mixed $item): bool => $item instanceof Section
                && str_starts_with((string) $item->getHeading(), '가입 신청 내용'));

        return $section?->isVisible() ? $section : null;
    }

    /**
     * One entry inside the section, by key.
     */
    private function entry(mixed $section, string $key): mixed
    {
        return collect($section->getChildSchema()->getComponents(withHidden: true))
            ->first(fn (mixed $item): bool => method_exists($item, 'getName') && $item->getName() === $key);
    }

    /**
     * The redaction notice inside the section, which is a Callout and so
     * carries a heading rather than a key.
     */
    private function redactionNotice(mixed $section): ?Callout
    {
        return collect($section->getChildSchema()->getComponents(withHidden: true))
            ->first(fn (mixed $item): bool => $item instanceof Callout);
    }

    /**
     * The resolved state of one entry inside the section.
     */
    private function entryState(mixed $section, string $key): mixed
    {
        return $this->entry($section, $key)?->getState();
    }
}
