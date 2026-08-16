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
 *
 * Five pages are 성도 전용 in full now - 교회 행사, 교회 소식, 자료실,
 * 헌금 and 앨범 - so anybody else gets their heading and one line
 * offering the login, and no query behind it is ever run.
 */
class ChurchMemberAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);

        /**
         * Neither fixture is named '성도 전용': the tag is asserted
         * absent from these pages, and a title carrying the phrase
         * would fail that on its own.
         */
        Announcement::factory()->create([
            'title' => '새가족 명단 공지',
            'slug' => 'members-only-notice',
            'content' => '<p>교적에 있는 분만 보십니다.</p>',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        Bulletin::factory()->create([
            'title' => '8월 3일 주일 예배 주보',
            'published_at' => now()->subDay(),
        ]);

        Album::factory()->create([
            'title' => '수련회 사진첩',
            'slug' => 'members-only-album',
            'is_published' => true,
        ]);
    }

    /**
     * The five pages that are 성도 전용 in full.
     *
     * @return array<int, string>
     */
    private function restrictedPages(): array
    {
        return ['/events', '/news', '/downloads', '/giving', '/album'];
    }

    /**
     * Somebody on the 교적 sees all of it.
     */
    public function test_a_member_sees_restricted_content(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create());

        $this->get('/news')->assertOk()->assertSee('새가족 명단 공지');
        $this->get('/downloads')->assertOk()->assertSee('8월 3일 주일 예배 주보');
        $this->get('/album')->assertOk()->assertSee('수련회 사진첩');

        foreach ($this->restrictedPages() as $page) {
            $this->get($page)->assertOk()->assertDontSee('section-members-only');
        }
    }

    /**
     * A guest gets the heading and the login offer on every one of them,
     * and nothing else.
     */
    public function test_a_guest_gets_the_sign_in_notice_on_every_page(): void
    {
        foreach ($this->restrictedPages() as $page) {
            $this->get($page)
                ->assertOk()
                ->assertSee('section-members-only')
                ->assertSee('로그인')
                ->assertSee('noindex', escape: false);
        }
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

        $this->actingAs(User::factory()->create());

        foreach ($this->restrictedPages() as $page) {
            $this->get($page)->assertOk()->assertSee('section-members-only');
        }

        $this->get('/news')->assertDontSee('새가족 명단 공지');
        $this->get('/downloads')->assertDontSee('8월 3일 주일 예배 주보');
        $this->get('/album')->assertDontSee('수련회 사진첩');
    }

    /**
     * A record's own page names the section, never the record.
     *
     * The slug carries the title, so a page that echoed it back would
     * hand over the one thing the login is standing in front of. The
     * home page still links notices to everybody, so this is the page a
     * guest most often lands on.
     */
    public function test_a_detail_page_names_the_section_rather_than_the_record(): void
    {
        foreach ([null, User::factory()->create()] as $reader) {
            if ($reader) {
                $this->actingAs($reader);
            }

            $this->get('/news/members-only-notice')
                ->assertOk()
                ->assertSee('교회 소식')
                ->assertDontSee('새가족 명단 공지')
                ->assertDontSee('교적에 있는 분만 보십니다');

            $this->get('/album/members-only-album')
                ->assertOk()
                ->assertSee('앨범')
                ->assertDontSee('수련회 사진첩');
        }
    }

    /**
     * The 성도 전용 tag is gone from the site.
     *
     * It marked a row on a page open to everybody. The page is closed
     * now, so on every one of them the tag would only repeat what
     * getting through the door already said.
     */
    public function test_the_tag_appears_on_none_of_the_pages(): void
    {
        foreach (['/', ...$this->restrictedPages()] as $page) {
            $this->get($page)->assertOk()->assertDontSee('성도 전용', escape: false);
        }

        $this->actingAs(User::factory()->create());

        foreach (['/', ...$this->restrictedPages()] as $page) {
            $this->get($page)->assertOk()->assertDontSee('성도 전용', escape: false);
        }

        /** Nor to a 성도, who is reading the real pages. */
        $this->actingAs(User::factory()->onTheRoster()->create());

        foreach (['/', ...$this->restrictedPages()] as $page) {
            $this->get($page)->assertOk()->assertDontSee('성도 전용', escape: false);
        }
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

        $this->actingAs(User::factory()->create())
            ->get('/giving')
            ->assertOk()
            ->assertDontSee('section-giving-records')
            ->assertDontSee('이영희')
            ->assertSee('section-members-only');
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
            ->assertDontSee('section-members-only');
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
            ->assertDontSee('새가족 명단 공지');
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
            ->assertDontSee('새가족 명단 공지');
    }
}
