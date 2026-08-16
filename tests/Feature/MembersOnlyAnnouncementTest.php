<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Database\Seeders\PositionSeeder;
use Database\Seeders\ServiceTypeSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers 교회 소식, which is published to 성도 on the 교적 only.
 *
 * The section is closed as a whole now rather than notice by notice,
 * so no notice carries an audience of its own. Every assertion here is
 * about what a guest's response contains, not about what is hidden in
 * the markup - the controller never runs the query, so no body is ever
 * rendered. The home page is the exception it always was: it lists the
 * latest titles to everybody, and links each one at the closed page.
 */
class MembersOnlyAnnouncementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The notice under test.
     */
    private Announcement $notice;

    /**
     * Seed the reference data the public pages rely on, then publish one
     * notice recent enough to reach the home page.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PositionSeeder::class,
            ServiceTypeSeeder::class,
            SiteSettingSeeder::class,
        ]);

        $this->notice = Announcement::factory()->pinned()->create([
            'title' => '7월 새가족 소개',
            'slug' => 'news-new-families',
            'content' => '<p>김철수 집사님 가정이 등록하셨습니다.</p>',
            'published_at' => now(),
        ]);
    }

    /**
     * The notices that prompted the old per-notice flag - the ones
     * naming new families, cell assignments and a volunteering contact
     * - are ordinary rows again, and the page they live on is what
     * keeps their names off a guest's screen.
     */
    public function test_the_notices_that_name_members_are_held_back_by_the_page(): void
    {
        $slugs = ['news-20260802-2', 'news-20260809', 'news-20260809-2'];

        $this->assertCount(3, Announcement::query()->visible()->whereIn('slug', $slugs)->get());

        $news = $this->get('/news')->assertOk();

        foreach (['유승희', '권미라', '권슬기', '전영주', '오승희'] as $name) {
            $news->assertDontSee($name);
        }
    }

    /**
     * And one of them reaches the home page's 최신 소식, which it did
     * not while the flag was still filtering a scope nobody could set.
     *
     * Its date is moved to now first: four notices share the seeded
     * 8월 9일, more than the band holds, so which of them lands in it
     * is otherwise down to how the database returns a tie.
     */
    public function test_a_notice_naming_members_now_reaches_the_home_page(): void
    {
        $cell = Announcement::query()->where('slug', 'news-20260809')->sole();
        $cell->update(['published_at' => now()]);

        $this->get('/')->assertOk()->assertSee($cell->title);
    }

    /**
     * A guest gets neither the title nor the body on the news list.
     */
    public function test_a_guest_receives_no_notice_on_the_news_list(): void
    {
        $this->get('/news')
            ->assertOk()
            ->assertDontSee('7월 새가족 소개')
            ->assertDontSee('김철수 집사님 가정');
    }

    /**
     * The home page names every notice to a guest and hands over none
     * of them: the title is the invitation to sign in, and the body is
     * on the page behind the 교적.
     */
    public function test_the_home_page_names_the_notice_to_a_guest(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('7월 새가족 소개')
            ->assertDontSee('김철수 집사님 가정');
    }

    /**
     * A guest hitting the detail URL directly gets the section's own
     * heading and the login offer - never the notice's title, which the
     * slug carries and which is the thing being kept back.
     *
     * It used to be a 404. The home page still lists news titles to
     * everybody, so that was a dead end where this is a next step.
     */
    public function test_a_guest_gets_the_sign_in_notice_on_the_detail_url(): void
    {
        $this->get('/news/news-new-families')
            ->assertOk()
            ->assertSee('교회 소식')
            ->assertSee('section-members-only')
            ->assertDontSee('7월 새가족 소개')
            ->assertDontSee('김철수 집사님 가정');
    }

    /**
     * The sitemap lists no notice at all, and not the section either.
     *
     * 교회 소식 answers a crawler with a login notice carrying noindex,
     * so listing the address would only ask to have that indexed.
     */
    public function test_the_sitemap_omits_the_notices_and_the_section(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee(route('news.show', $this->notice))
            ->assertDontSee(route('news.index'));
    }

    /**
     * Signed in as a 성도 the sitemap is the same document, because it
     * is written for crawlers and may be cached by the CDN.
     */
    public function test_the_sitemap_is_the_same_for_a_member(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee(route('news.show', $this->notice))
            ->assertDontSee(route('news.index'));
    }

    /**
     * A signed-in 성도 sees the notice on the list and on its detail
     * page, and neither carries a 성도 전용 tag any more: the whole page
     * is, so the row does not need saying so.
     */
    public function test_a_signed_in_member_sees_the_notice_without_a_tag(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/news')
            ->assertOk()
            ->assertSee('7월 새가족 소개')
            ->assertDontSee('성도 전용');

        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/news/news-new-families')
            ->assertOk()
            ->assertSee('김철수 집사님 가정이 등록하셨습니다.')
            ->assertDontSee('성도 전용');
    }

    /**
     * The 하이라이트 band is the same for everybody.
     *
     * It used to hide itself from a guest whenever the flagged notice
     * was 성도 전용, which is how an admin flagging 셀 배정 emptied the
     * front page for every visitor without being told.
     */
    public function test_the_highlight_reaches_a_guest_and_a_member_alike(): void
    {
        $this->notice->update(['is_highlighted' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSee('section-highlight')
            ->assertSee('7월 새가족 소개');

        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/')
            ->assertOk()
            ->assertSee('section-highlight')
            ->assertSee('7월 새가족 소개');
    }
}
