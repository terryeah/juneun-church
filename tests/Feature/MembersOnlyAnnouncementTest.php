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
 * Covers 성도 전용 소식: notices that name individual members and so are
 * published to signed-in 성도 only.
 *
 * Every assertion here is about what a guest's response contains, not
 * about what is hidden in the markup - a restricted notice is excluded
 * from the query, so neither its title nor its body is ever rendered.
 */
class MembersOnlyAnnouncementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The restricted notice under test.
     */
    private Announcement $restricted;

    /**
     * Seed the reference data the public pages rely on, then publish one
     * restricted notice recent enough to reach the home page.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PositionSeeder::class,
            ServiceTypeSeeder::class,
            SiteSettingSeeder::class,
        ]);

        $this->restricted = Announcement::factory()->membersOnly()->pinned()->create([
            'title' => '7월 새가족 소개',
            'slug' => 'news-new-families',
            'content' => '<p>김철수 집사님 가정이 등록하셨습니다.</p>',
            'published_at' => now(),
        ]);
    }

    /**
     * The notices that prompted the switch - the ones already published
     * naming new families, cell assignments and a volunteering contact -
     * are restricted by migration, and their names never reach a guest.
     */
    public function test_the_migration_restricts_the_notices_that_name_members(): void
    {
        $restricted = Announcement::query()
            ->whereIn('slug', ['news-20260802-2', 'news-20260809', 'news-20260809-2'])
            ->get();

        $this->assertCount(3, $restricted);
        $this->assertTrue($restricted->every->is_members_only);

        $news = $this->get('/news')->assertOk();

        foreach (['유승희', '권미라', '권슬기', '전영주', '오승희'] as $name) {
            $news->assertDontSee($name);
        }
    }

    /**
     * A guest gets neither the title nor the body on the news list.
     */
    public function test_a_guest_does_not_receive_a_restricted_notice_on_the_news_list(): void
    {
        $this->get('/news')
            ->assertOk()
            ->assertDontSee('7월 새가족 소개')
            ->assertDontSee('김철수 집사님 가정');
    }

    /**
     * A guest gets neither the title nor the body on the home page.
     */
    public function test_a_guest_does_not_receive_a_restricted_notice_on_the_home_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('7월 새가족 소개')
            ->assertDontSee('김철수 집사님 가정');
    }

    /**
     * A guest hitting the detail URL directly gets a 404, not a 403: a
     * 403 would confirm the notice exists at that slug.
     */
    public function test_a_guest_gets_a_404_on_the_detail_url(): void
    {
        $this->get('/news/news-new-families')
            ->assertNotFound()
            ->assertDontSee('김철수 집사님 가정');
    }

    /**
     * The sitemap never lists a restricted notice.
     */
    public function test_the_sitemap_omits_a_restricted_notice(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee(route('news.show', $this->restricted));
    }

    /**
     * Signed in as an admin the sitemap still omits it, because the
     * document is written for crawlers and may be cached by the CDN.
     */
    public function test_the_sitemap_omits_a_restricted_notice_even_for_a_member(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee(route('news.show', $this->restricted));
    }

    /**
     * A signed-in 성도 sees the notice on the list and on its detail
     * page, both carrying the 성도 전용 badge.
     */
    public function test_a_signed_in_member_sees_the_notice_with_the_badge(): void
    {
        $badge = 'inline-flex items-center rounded-md border border-success bg-slate-900 px-2 py-0.5 align-middle font-kr text-xs font-medium text-success';

        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/news')
            ->assertOk()
            ->assertSee('7월 새가족 소개')
            ->assertSee($badge, false)
            ->assertSee('성도 전용');

        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/news/news-new-families')
            ->assertOk()
            ->assertSee('김철수 집사님 가정이 등록하셨습니다.')
            ->assertSee($badge, false)
            ->assertSee('성도 전용');
    }

    /**
     * A restricted notice holding the 하이라이트 flag leaves the home page
     * band absent for a guest, exactly as when nothing is flagged, and
     * shows normally once signed in.
     */
    public function test_a_restricted_highlight_does_not_reach_a_guest(): void
    {
        $this->restricted->update(['is_highlighted' => true]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('section-highlight')
            ->assertDontSee('7월 새가족 소개');

        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/')
            ->assertOk()
            ->assertSee('section-highlight')
            ->assertSee('7월 새가족 소개');
    }
}
