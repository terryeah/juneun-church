<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Announcement;
use App\Models\Bulletin;
use App\Models\Member;
use App\Models\MembershipRequest;
use App\Models\Offering;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers what 성도 전용 actually answers to.
 *
 * Signing in is not it. Anybody may send a 가입 신청, so an approved
 * account proves only that the office let somebody in, not that the
 * church recognises them. The 교적 record is what says that, and it is
 * the only thing these pages ask about.
 */
class ChurchMemberAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);

        Announcement::factory()->create([
            'title' => '성도 전용 공지',
            'slug' => 'members-only-notice',
            'content' => '<p>교적에 있는 분만 보십니다.</p>',
            'is_published' => true,
            'is_members_only' => true,
            'published_at' => now()->subDay(),
        ]);

        Bulletin::factory()->create([
            'title' => '8월 3일 주일 예배 주보',
            'is_members_only' => true,
            'published_at' => now()->subDay(),
        ]);

        Album::factory()->create([
            'title' => '성도 전용 앨범',
            'slug' => 'members-only-album',
            'is_published' => true,
            'is_members_only' => true,
        ]);
    }

    /**
     * Somebody on the 교적 sees all of it.
     */
    public function test_a_member_sees_restricted_content(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create());

        $this->get('/news')->assertOk()->assertSee('성도 전용 공지');
        $this->get('/downloads')->assertOk()->assertSee('8월 3일 주일 예배 주보');
        $this->get('/gallery')->assertOk()->assertSee('성도 전용 앨범');
    }

    /**
     * A 일반회원 is signed in and still sees none of it.
     *
     * This is the whole point of the change: before, being signed in
     * was the test, so approving somebody the church had not registered
     * handed them the bulletins and the giving records.
     */
    public function test_a_general_member_sees_none_of_it(): void
    {
        $this->seed(RoleSeeder::class);

        $account = User::factory()->create();

        $this->actingAs($account);

        $this->get('/news')->assertOk()->assertDontSee('성도 전용 공지');
        $this->get('/downloads')->assertOk()->assertDontSee('8월 3일 주일 예배 주보');
        $this->get('/gallery')->assertOk()->assertDontSee('성도 전용 앨범');
        $this->get('/news/members-only-notice')->assertNotFound();
        $this->get('/gallery/members-only-album')->assertNotFound();
    }

    /**
     * The 성도 전용 tag never reaches somebody who is not one.
     *
     * A restricted item is dropped from the response rather than hidden
     * in it, so most of these tags cannot be reached anyway - they ride
     * on the notice or album they mark. 자료실 was the exception: its
     * tag sits beside the page title and so was drawn for everybody,
     * naming files the reader could not see and advertising that the
     * church holds some back.
     */
    public function test_the_tag_is_never_shown_to_somebody_who_is_not_a_member(): void
    {
        foreach (['/', '/news', '/downloads', '/gallery', '/giving'] as $page) {
            $this->get($page)->assertOk()->assertDontSee('성도 전용', escape: false);
        }

        /** Nor to a signed-in 일반회원, who sees the same pages a guest does. */
        $this->actingAs(User::factory()->create());

        foreach (['/', '/news', '/downloads', '/gallery', '/giving'] as $page) {
            $this->get($page)->assertOk()->assertDontSee('성도 전용', escape: false);
        }
    }

    /**
     * A 성도 is still told which pages hold restricted material.
     */
    public function test_the_tag_is_shown_to_a_member(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create());

        $this->get('/downloads')->assertOk()->assertSee('성도 전용');
        $this->get('/news')->assertOk()->assertSee('성도 전용');
        $this->get('/gallery')->assertOk()->assertSee('성도 전용');
    }

    /**
     * The giving records follow the same rule, and a 일반회원 is shown
     * the same notice a guest is rather than an empty page.
     */
    public function test_the_giving_records_follow_the_roster(): void
    {
        Offering::create([
            'sunday_date' => '2026-07-26',
            'items' => [['category' => '십일조', 'name' => '이영희', 'amount' => '222.00']],
        ]);

        /**
         * The section is identified by its class rather than by its
         * wording: the sign-in notice says '주보에 실리는 주일 헌금
         * 내역은 성도에게만 공개됩니다', so asserting on the phrase
         * would match the very notice that proves the records are gone.
         */
        $this->actingAs(User::factory()->create())
            ->get('/giving')
            ->assertOk()
            ->assertDontSee('section-giving-records')
            ->assertSee('section-giving-signup');
    }

    /**
     * And a 성도 is shown them.
     */
    public function test_a_member_sees_the_giving_records(): void
    {
        Offering::create([
            'sunday_date' => '2026-07-26',
            'items' => [['category' => '십일조', 'name' => '이영희', 'amount' => '222.00']],
        ]);

        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/giving')
            ->assertOk()
            ->assertSee('section-giving-records')
            ->assertDontSee('section-giving-signup');
    }

    /**
     * A staff account off the 교적 is treated the same way.
     *
     * One rule, no exemptions. Staff logins are only ever handed to
     * people already on the roster, so this costs nobody anything - and
     * it keeps 성도 전용 meaning one thing rather than two.
     */
    public function test_a_staff_account_off_the_roster_is_no_exception(): void
    {
        $this->seed(RoleSeeder::class);

        $staff = User::factory()->create();
        $staff->assignRole('admin');

        $this->actingAs($staff)
            ->get('/news')
            ->assertOk()
            ->assertDontSee('성도 전용 공지');
    }

    /**
     * Approving a 가입 신청 without putting the applicant on the 교적
     * leaves them a 일반회원, and the roster is untouched.
     */
    public function test_an_approval_can_leave_the_applicant_off_the_roster(): void
    {
        $this->seed(RoleSeeder::class);

        $reviewer = User::factory()->create();
        $before = Member::query()->count();

        $request = MembershipRequest::create([
            'name' => '방문자',
            'birth_date' => '1990-01-01',
            'phone' => '0400 000 000',
            'email' => 'visitor@example.com',
            'password' => 'secret-password',
        ]);

        $account = $request->approve(null, $reviewer, '기타', registerOnRoster: false);

        $this->assertFalse($account->isChurchMember());
        $this->assertTrue($account->hasRole('general_member'));
        $this->assertSame($before, Member::query()->count(), '교적이 늘어나면 안 됩니다.');
        $this->assertNull($request->fresh()->matched_member_id);

        $this->actingAs($account)
            ->get('/news')
            ->assertOk()
            ->assertDontSee('성도 전용 공지');
    }
}
