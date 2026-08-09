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
 * Both kinds of file default to 성도 전용, so as with the notices that
 * name members the assertions are about what reaches a guest's
 * response: a restricted file leaves the query, and neither its title
 * nor the URL of its PDF is ever rendered.
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
            'is_members_only' => true,
        ]);

        Document::factory()->create([
            'title' => '새가족 등록 카드',
            'description' => '새로 오신 분이 작성하는 카드입니다.',
            'file_path' => 'documents/new-family.pdf',
            'published_at' => '2026-08-01',
            'is_members_only' => true,
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
     * A guest gets neither file, and is offered a way in.
     */
    public function test_a_guest_receives_no_files(): void
    {
        $this->get('/downloads')
            ->assertOk()
            ->assertDontSee('bulletin-2026-08-09.pdf')
            ->assertDontSee('new-family.pdf')
            ->assertSee('자료실은 로그인 후 보실 수 있습니다')
            ->assertDontSee('등록된 자료가 없습니다.');

        $this->get('/downloads?type=documents')
            ->assertOk()
            ->assertDontSee('new-family.pdf')
            ->assertDontSee('새가족 등록 카드');
    }

    /**
     * A signed-in 성도 gets the 주보 tab by default.
     */
    public function test_a_member_sees_the_bulletins_tab_first(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/downloads')
            ->assertOk()
            ->assertSee('주일 예배 주보')
            ->assertSee('bulletin-2026-08-09.pdf')
            ->assertDontSee('새가족 등록 카드');
    }

    /**
     * The 문서 tab shows the documents and their one-line notes.
     */
    public function test_the_documents_tab_shows_documents(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/downloads?type=documents')
            ->assertOk()
            ->assertSee('새가족 등록 카드')
            ->assertSee('새로 오신 분이 작성하는 카드입니다.')
            ->assertDontSee('주일 예배 주보');
    }

    /**
     * An unknown tab falls back to 주보 rather than erroring.
     */
    public function test_an_unknown_tab_falls_back(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/downloads?type=nonsense')
            ->assertOk()
            ->assertSee('주일 예배 주보');
    }

    /**
     * A document the church opens deliberately reaches a guest, and
     * with nothing else held back the sign-in offer stays away.
     */
    public function test_an_open_document_reaches_a_guest(): void
    {
        Bulletin::query()->update(['is_members_only' => false]);
        Document::query()->update(['is_members_only' => false]);

        $this->get('/downloads?type=documents')
            ->assertOk()
            ->assertSee('새가족 등록 카드')
            ->assertDontSee('자료실은 로그인 후 보실 수 있습니다');
    }

    /**
     * A document defaults to restricted, so one uploaded without a
     * thought is closed rather than open.
     */
    public function test_a_document_is_restricted_by_default(): void
    {
        $document = Document::create([
            'title' => '기본값 확인',
            'file_path' => 'documents/default.pdf',
            'published_at' => '2026-08-16',
        ]);

        $this->assertTrue($document->fresh()->is_members_only);
    }
}
