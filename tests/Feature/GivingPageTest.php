<?php

namespace Tests\Feature;

use App\Models\Offering;
use App\Models\User;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Covers the giving page (헌금): week selection, and the login standing
 * in front of the whole page rather than only the records.
 */
class GivingPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the settings the page renders plus two weeks of offerings.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);

        /** A migration seeds a real 2026-07-19 offering, so start from a known set. */
        Offering::query()->delete();

        Offering::create([
            'sunday_date' => '2026-07-19',
            'items' => [['category' => '십일조', 'name' => '김철수', 'amount' => '111.00']],
        ]);

        Offering::create([
            'sunday_date' => '2026-07-26',
            'items' => [['category' => '십일조', 'name' => '이영희', 'amount' => '222.00']],
        ]);
    }

    /**
     * Without a week parameter the most recent Sunday is rendered.
     */
    public function test_the_latest_week_is_shown_by_default(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/giving')
            ->assertOk()
            ->assertSee('이영희')
            ->assertDontSee('김철수');
    }

    /**
     * Requesting an earlier week renders that week's figures only.
     */
    public function test_a_requested_week_is_shown_instead_of_the_latest(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/giving?week=2026-07-19')
            ->assertOk()
            ->assertSee('2026년 7월 19일 주일 헌금 내역')
            ->assertSee('김철수')
            ->assertSee('$111.00')
            ->assertDontSee('이영희')
            ->assertDontSee('$222.00');
    }

    /**
     * An unknown week falls back to the most recent Sunday.
     */
    public function test_an_unknown_week_falls_back_to_the_latest(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/giving?week=1999-01-03')
            ->assertOk()
            ->assertSee('이영희');
    }

    /**
     * A guest gets the heading and the login offer, and not even the
     * bank details - the page is 성도 전용 in full now.
     */
    public function test_a_guest_receives_only_the_sign_in_notice(): void
    {
        $this->get('/giving')
            ->assertOk()
            ->assertSee('section-members-only')
            ->assertDontSee('이체 시 참조란에 이름과 헌금 종류를 약자로 함께 적어주세요.', false)
            ->assertDontSee('section-giving-records')
            ->assertDontSee('지난 주일 보기')
            ->assertDontSee('이영희');
    }

    /**
     * Signed-in 성도 see the bank details and the records, with no tag
     * on either: the page itself is the tag.
     */
    public function test_members_receive_the_records_section(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/giving')
            ->assertOk()
            ->assertSee('이체 시 참조란에 이름과 헌금 종류를 약자로 함께 적어주세요.', false)
            ->assertSee('section-giving-records')
            ->assertDontSee('성도 전용');
    }

    /**
     * The descriptive line is gone from under the heading, and stands
     * with the accounts instead.
     *
     * Cut from the header, it was cut from the page altogether, which
     * dropped the reader straight from 헌금 into a grid of bank
     * accounts with nothing saying what the giving is for.
     */
    public function test_the_lead_paragraph_moved_below_the_accounts(): void
    {
        $page = $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/giving')
            ->assertOk()
            ->assertSee('받은 은혜에 감사하며 드리는 헌금은')
            ->getContent();

        $heading = Str::before($page, 'section-giving-details');

        $this->assertStringNotContainsString('받은 은혜에 감사하며 드리는 헌금은', $heading);
    }

    /**
     * The notice says the right thing to each of its two readers.
     *
     * The gate is the 교적, not the session. A 일반회원 told to 로그인 is
     * being sent to a form they have already filled in, while the header
     * beside it shows 로그아웃; what they need is the words 교적 등록 and
     * the office's address.
     */
    public function test_the_notice_asks_a_signed_in_reader_for_the_roster_rather_than_a_login(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/giving')
            ->assertOk()
            ->assertSee('교적에 등록된 계정이 아닙니다')
            ->assertSee('juneunchurch@gmail.com')
            ->assertDontSee('후 확인해 주세요');
    }

    /**
     * A guest still gets the login, and it carries the page they were
     * reading so signing in does not strand them somewhere else.
     */
    public function test_the_notice_hands_a_guest_a_login_that_returns_them(): void
    {
        $this->get('/downloads')
            ->assertOk()
            ->assertSee('후 확인해 주세요')
            ->assertSee(route('login', ['next' => '/downloads']), escape: false)
            ->assertDontSee('교적에 등록된 계정이 아닙니다');
    }
}
