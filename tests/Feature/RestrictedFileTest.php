<?php

namespace Tests\Feature;

use App\Models\Bulletin;
use App\Models\Document;
use App\Models\User;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Covers 성도 전용 files being served by the application rather than by
 * the CDN.
 *
 * The media bucket is public, so a restricted PDF used to be protected
 * only by a name nobody could guess - concealment, not access control,
 * and unobservable: the CDN answers those requests, so the church would
 * never know a link had been forwarded.
 */
class RestrictedFileTest extends TestCase
{
    use RefreshDatabase;

    private Bulletin $restricted;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);
        Storage::fake(config('filesystems.media'));

        $this->restricted = Bulletin::factory()->create([
            'title' => '8월 3일 주일 예배 주보',
            'file_path' => 'bulletins/restricted.pdf',
            'published_at' => '2026-08-03',
        ]);

        Storage::disk(config('filesystems.media'))->put('bulletins/restricted.pdf', '%PDF-1.4 restricted');
    }

    /**
     * Every 주보 and every 문서 is linked through the application, never
     * at the object, and there is no record left that can opt out of
     * it: the flag that used to grant one is gone from the table.
     */
    public function test_the_file_is_routed_through_the_site(): void
    {
        $this->assertSame($this->restricted->pdfUrl(), $this->restricted->fileUrl());
        $this->assertStringNotContainsString('bulletins/restricted.pdf', $this->restricted->fileUrl());

        $document = Document::factory()->create(['file_path' => 'documents/routed.pdf']);

        $this->assertSame(route('document.file', $document), $document->fileUrl());
        $this->assertStringNotContainsString('documents/routed.pdf', $document->fileUrl());
    }

    /**
     * Rotating gives every file a fresh address and writes it private.
     *
     * The uploads that are already on the bucket were written public,
     * and the bucket is served by the CDN, so each of them answers
     * anyone holding its URL for a year at a time - the round trip
     * through this controller counts for nothing until they are
     * rewritten. Every row is covered, not only the ones once ticked
     * 성도 전용: 자료실 is closed as a whole page.
     */
    public function test_rotating_gives_every_file_a_fresh_private_address(): void
    {
        $disk = Storage::disk(config('filesystems.media'));

        $document = Document::factory()->create(['file_path' => 'documents/public.pdf']);
        $disk->put('documents/public.pdf', '%PDF-1.4', ['visibility' => 'public']);
        $disk->setVisibility('bulletins/restricted.pdf', 'public');

        $this->artisan('files:rotate-restricted')->assertSuccessful();

        foreach ([$this->restricted->fresh(), $document->fresh()] as $record) {
            $this->assertNotContains($record->file_path, ['bulletins/restricted.pdf', 'documents/public.pdf']);
            $this->assertTrue($disk->exists($record->file_path));
            $this->assertSame('private', $disk->getVisibility($record->file_path));
        }

        $this->assertFalse($disk->exists('bulletins/restricted.pdf'));
        $this->assertFalse($disk->exists('documents/public.pdf'));
    }

    /**
     * A guest asking for the file itself gets nothing, and gets it as a
     * 404 rather than a 403 - the same answer a file that does not
     * exist gives, so the address confirms nothing.
     */
    public function test_a_guest_cannot_fetch_a_restricted_file(): void
    {
        $this->get($this->restricted->pdfUrl())->assertNotFound();
    }

    /**
     * The 주보 page, though, is a page: a guest gets the sign-in notice
     * the other 성도 전용 pages give, because a link forwarded into a
     * 단톡방 is opened by 성도 whose session has lapsed and a bare 404
     * leaves them nowhere. It names neither the 주보 nor its date, and
     * an address with no record behind it answers the same way.
     */
    public function test_a_guest_gets_the_sign_in_notice_on_the_page(): void
    {
        $this->get(route('bulletin.file', $this->restricted))
            ->assertOk()
            ->assertSee('section-members-only')
            ->assertSee('성도에게만 공개됩니다')
            ->assertSee('noindex', false)
            ->assertDontSee($this->restricted->title)
            ->assertDontSee('2026년 8월 3일');

        $this->get(route('bulletin.file', 99999))->assertOk()->assertSee('section-members-only');
    }

    /**
     * A signed-in 일반회원, who is not on the 교적, keeps the 404, so
     * working through the addresses one by one still learns nothing.
     */
    public function test_a_general_member_cannot_fetch_it_either(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('bulletin.file', $this->restricted))
            ->assertNotFound();

        $this->actingAs(User::factory()->create())
            ->get($this->restricted->pdfUrl())
            ->assertNotFound();
    }

    /**
     * A record whose file went missing 404s at the file itself.
     */
    public function test_a_missing_file_is_not_found(): void
    {
        Storage::disk(config('filesystems.media'))->delete('bulletins/restricted.pdf');

        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get($this->restricted->pdfUrl())
            ->assertNotFound();
    }

    /**
     * The name in the address is part of the record, not decoration:
     * any date-shaped name used to serve any 주보, so a forwarded link
     * could name the wrong Sunday and one file had endless addresses
     * for the CDN to keep separately.
     */
    public function test_a_wrong_name_redirects_to_the_canonical_one(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get(route('bulletin.pdf', [$this->restricted, 'Bulletin_1111_11_11.pdf']))
            ->assertRedirect($this->restricted->pdfUrl())
            ->assertStatus(301);
    }

    /**
     * A 성도 gets the file, and no shared cache may keep a copy of it.
     */
    public function test_a_member_receives_the_file(): void
    {
        $response = $this->actingAs(User::factory()->onTheRoster()->create())
            ->get($this->restricted->pdfUrl())
            ->assertOk();

        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    /**
     * The file arrives named for its Sunday, in ASCII.
     *
     * A Hangul title went through Str::ascii() to nothing, leaving the
     * browser to name the file after the last part of the address - the
     * record's id, so every 주보 saved as a file called "5".
     */
    public function test_the_file_is_named_for_its_date(): void
    {
        $disposition = (string) $this->actingAs(User::factory()->onTheRoster()->create())
            ->get($this->restricted->pdfUrl())
            ->headers->get('Content-Disposition');

        $this->assertSame('inline; filename=Bulletin_2026_08_03.pdf', $disposition);
    }

    /**
     * A 문서 keeps its Korean title, which is the name worth having.
     *
     * The header carries both halves: the real name in filename*, which
     * every current browser saves under, and the date-based name as the
     * ASCII fallback. Only the fallback used to be sent, so every
     * Korean title arrived as Document_<날짜>.pdf and two documents
     * published on one day were saved over each other.
     */
    public function test_a_document_keeps_its_korean_name(): void
    {
        $document = Document::factory()->create([
            'title' => '새가족 등록 카드',
            'file_path' => 'documents/korean.pdf',
            'published_at' => '2026-08-16',
        ]);

        Storage::disk(config('filesystems.media'))->put('documents/korean.pdf', '%PDF-1.4');

        $disposition = (string) $this->actingAs(User::factory()->onTheRoster()->create())
            ->get(route('document.file', $document))
            ->assertOk()
            ->headers->get('Content-Disposition');

        $this->assertSame(
            "inline; filename=Document_2026_08_16.pdf; filename*=utf-8''".rawurlencode('새가족 등록 카드.pdf'),
            $disposition,
        );
    }

    /**
     * The bare address hands a 성도 straight to the PDF.
     *
     * 자료실 links to the file, so this is only what an older link or a
     * forwarded message carries. Nothing is drawn around the PDF.
     */
    public function test_the_bare_address_leads_to_the_pdf(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get(route('bulletin.file', $this->restricted))
            ->assertRedirect($this->restricted->pdfUrl());
    }

    /**
     * A 문서 answers the same way.
     */
    public function test_a_restricted_document_follows_the_same_rule(): void
    {
        $document = Document::factory()->create([
            'title' => '새가족 등록 카드',
            'file_path' => 'documents/restricted.pdf',
        ]);

        Storage::disk(config('filesystems.media'))->put('documents/restricted.pdf', '%PDF-1.4');

        $this->get(route('document.file', $document))->assertNotFound();

        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get(route('document.file', $document))
            ->assertOk();
    }

    /**
     * A separator in a title used to reach Symfony's header builder and
     * throw, so an admin could 500 the endpoint by typing a slash.
     */
    public function test_a_title_with_a_slash_still_serves(): void
    {
        $document = Document::factory()->create([
            'title' => '재정/지출 결의서',
            'file_path' => 'documents/slash.pdf',
            'published_at' => '2026-08-16',
        ]);

        Storage::disk(config('filesystems.media'))->put('documents/slash.pdf', '%PDF-1.4');

        $disposition = (string) $this->actingAs(User::factory()->onTheRoster()->create())
            ->get(route('document.file', $document))
            ->assertOk()
            ->headers->get('Content-Disposition');

        $this->assertSame(
            "inline; filename=Document_2026_08_16.pdf; filename*=utf-8''".rawurlencode('재정지출 결의서.pdf'),
            $disposition,
        );
    }
}
