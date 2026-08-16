<?php

namespace Tests\Feature;

use App\Models\Bulletin;
use App\Models\Document;
use App\Models\User;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers 자료실, which gathers the 주보 and the church's documents
 * behind two tabs.
 *
 * The page is 성도 전용 in full, so the assertions are about what
 * reaches a guest's response: the controller never runs the query, and
 * neither a title nor the URL of a PDF is ever rendered.
 */
class DownloadsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);

        Bulletin::factory()->create([
            'title' => '주일 예배 주보',
            'file_path' => 'bulletins/bulletin-2026-08-09.pdf',
            'published_at' => '2026-08-09',
        ]);

        Document::factory()->create([
            'title' => '새가족 등록 카드',
            'description' => '새로 오신 분이 작성하는 카드입니다.',
            'file_path' => 'documents/new-family.pdf',
            'published_at' => '2026-08-01',
        ]);
    }

    /**
     * The old 주보 address now points at 자료실, so anything linking to
     * it - a bookmark, a search result - still arrives somewhere useful.
     */
    public function test_the_old_bulletins_url_redirects(): void
    {
        $this->get('/bulletins')->assertRedirect('/downloads');
    }

    /**
     * A guest gets neither file, no tabs, and one way in.
     */
    public function test_a_guest_receives_no_files(): void
    {
        $this->get('/downloads')
            ->assertOk()
            ->assertDontSee('bulletin-2026-08-09.pdf')
            ->assertDontSee('new-family.pdf')
            ->assertSee('section-members-only')
            ->assertSee('성도에게만 공개됩니다')
            ->assertDontSee('등록된 자료가 없습니다.')
            ->assertDontSee('data-download-tab', escape: false);

        $this->get('/downloads?type=documents')
            ->assertOk()
            ->assertDontSee('new-family.pdf')
            ->assertDontSee('새가족 등록 카드');
    }

    /**
     * The descriptive line under the heading is gone from the page.
     */
    public function test_the_lead_paragraph_is_gone(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/downloads')
            ->assertOk()
            ->assertDontSee('주일 주보와 교회에서 쓰는 서식을 내려받는 곳입니다');
    }

    /**
     * No 성도 전용 tag beside the heading any more: only 성도 get here,
     * so it named nothing they could not already see.
     */
    public function test_the_tag_is_gone_from_the_heading(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/downloads')
            ->assertOk()
            ->assertDontSee('성도 전용');
    }

    /**
     * A signed-in 성도 gets the 주보 tab by default.
     */
    public function test_a_member_sees_the_bulletins_tab_first(): void
    {
        $bulletin = Bulletin::query()->sole();

        /**
         * The link goes through the site, not to the bucket. This used
         * to assert that the file's own CDN path appeared on the page -
         * which was the leak: that address answers anyone who has it,
         * for ever, whether they are a 성도 or not.
         */
        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/downloads')
            ->assertOk()
            ->assertSee('주일 예배 주보')
            ->assertSee(route('bulletin.file', $bulletin))
            ->assertDontSee('bulletins/bulletin-2026-08-09.pdf')
            ->assertDontSee('새가족 등록 카드');
    }

    /**
     * The 문서 tab shows the documents and their one-line notes.
     */
    public function test_the_documents_tab_shows_documents(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/downloads?type=documents')
            ->assertOk()
            ->assertSee('새가족 등록 카드')
            ->assertSee('새로 오신 분이 작성하는 카드입니다.')
            ->assertDontSee('주일 예배 주보');
    }

    /**
     * Each row says what it opens.
     *
     * A 주보 row leads to a page of the site now, so promising a PDF in
     * a new window was two things untrue at once; a 문서 row is still
     * the file itself.
     */
    public function test_a_row_names_what_it_opens(): void
    {
        $member = User::factory()->onTheRoster()->create();

        $bulletins = (string) $this->actingAs($member)
            ->get('/downloads')
            ->assertOk()
            ->assertSee('주보 보기 →')
            ->assertDontSee('PDF 보기 →')
            ->getContent();

        $documents = (string) $this->actingAs($member)
            ->get('/downloads?type=documents')
            ->assertOk()
            ->assertSee('PDF 보기 →')
            ->assertDontSee('주보 보기 →')
            ->getContent();

        /**
         * The header and the footer carry their own new-tab links, so
         * the assertion is made against the row's own opening tag
         * rather than against the whole page.
         */
        preg_match('~<a href="[^"]*/downloads/bulletin/\d+"[^>]*>~', $bulletins, $bulletinRow);
        preg_match('~<a href="[^"]*/downloads/document/\d+"[^>]*>~', $documents, $documentRow);

        $this->assertStringContainsString('aria-label="주일 예배 주보 보기"', $bulletinRow[0] ?? '');
        $this->assertStringNotContainsString('target=', $bulletinRow[0] ?? '');

        $this->assertStringContainsString('aria-label="새가족 등록 카드 PDF 열기 (새 창)"', $documentRow[0] ?? '');
        $this->assertStringContainsString('target="_blank"', $documentRow[0] ?? '');
    }

    /**
     * An unknown tab falls back to 주보 rather than erroring.
     */
    public function test_an_unknown_tab_falls_back(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/downloads?type=nonsense')
            ->assertOk()
            ->assertSee('주일 예배 주보');
    }

    /**
     * The 문서 tab is closed to a guest as flatly as the 주보 one: the
     * page is what is restricted, so there is no per-record flag left
     * for an upload to be forgotten on.
     */
    public function test_the_documents_tab_is_closed_too(): void
    {
        $this->get('/downloads?type=documents')
            ->assertOk()
            ->assertDontSee('새가족 등록 카드')
            ->assertSee('section-members-only');
    }
}
