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

    private Bulletin $open;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);
        Storage::fake(config('filesystems.media'));

        $this->restricted = Bulletin::factory()->create([
            'title' => '8월 3일 주일 예배 주보',
            'file_path' => 'bulletins/restricted.pdf',
            'is_members_only' => true,
        ]);

        $this->open = Bulletin::factory()->create([
            'title' => '누구나 보는 주보',
            'file_path' => 'bulletins/open.pdf',
            'is_members_only' => false,
        ]);

        Storage::disk(config('filesystems.media'))->put('bulletins/restricted.pdf', '%PDF-1.4 restricted');
        Storage::disk(config('filesystems.media'))->put('bulletins/open.pdf', '%PDF-1.4 open');
    }

    /**
     * A restricted file is linked through the application; an open one
     * keeps its direct CDN address, because there is nothing to check.
     */
    public function test_only_a_restricted_file_is_routed_through_the_site(): void
    {
        $this->assertSame(route('bulletin.file', $this->restricted), $this->restricted->fileUrl());
        $this->assertStringContainsString('bulletins/open.pdf', $this->open->fileUrl());
        $this->assertStringNotContainsString('/downloads/', $this->open->fileUrl());
    }

    /**
     * A guest asking for the file itself gets nothing, and gets it as a
     * 404 rather than a 403 - the same answer a file that does not
     * exist gives, so the address confirms nothing.
     */
    public function test_a_guest_cannot_fetch_a_restricted_file(): void
    {
        $this->get(route('bulletin.file', $this->restricted))->assertNotFound();
    }

    /**
     * Nor a signed-in 일반회원, who is not on the 교적.
     */
    public function test_a_general_member_cannot_fetch_it_either(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('bulletin.file', $this->restricted))
            ->assertNotFound();
    }

    /**
     * A 성도 gets the file, and no shared cache may keep a copy of it.
     */
    public function test_a_member_receives_the_file(): void
    {
        $response = $this->actingAs(User::factory()->onTheRoster()->create())
            ->get(route('bulletin.file', $this->restricted))
            ->assertOk();

        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }

    /**
     * A 문서 answers the same way.
     */
    public function test_a_restricted_document_follows_the_same_rule(): void
    {
        $document = Document::factory()->create([
            'title' => '새가족 등록 카드',
            'file_path' => 'documents/restricted.pdf',
            'is_members_only' => true,
        ]);

        Storage::disk(config('filesystems.media'))->put('documents/restricted.pdf', '%PDF-1.4');

        $this->get(route('document.file', $document))->assertNotFound();

        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get(route('document.file', $document))
            ->assertOk();
    }
}
