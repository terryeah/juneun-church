<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Bulletin;
use App\Models\Member;
use App\Models\Photo;
use App\Services\CloudflareCachePurger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Deleting a photograph or a bulletin has to take it off the edge as
 * well as out of the bucket.
 *
 * R2 uploads carry a one-year immutable Cache-Control header and
 * media.juneun.com caches on it, so without a purge a deleted object
 * stays retrievable at its URL for a year by anyone who kept the link.
 */
class CdnCachePurgeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pretend the Cloudflare credentials are configured and intercept
     * every purge call.
     */
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.cloudflare.api_token', 'test-token');
        config()->set('services.cloudflare.zone_id', 'test-zone');

        Storage::fake('public');
        config()->set('filesystems.media', 'public');

        Http::fake(['api.cloudflare.com/*' => Http::response(['success' => true])]);
    }

    /**
     * The URLs purged after the response, in the order they were sent.
     *
     * @return list<list<string>>
     */
    private function purgedBatches(): array
    {
        CloudflareCachePurger::flush();

        return Http::recorded()
            ->map(fn (array $exchange): array => $exchange[0]->data()['files'])
            ->all();
    }

    /**
     * Deleting a photograph purges the image and its thumbnail.
     */
    public function test_deleting_a_photo_purges_both_of_its_objects(): void
    {
        $album = Album::factory()->create();

        Photo::create([
            'album_id' => $album->id,
            'filename' => 'a.webp',
            'original_filename' => 'a.jpg',
            'path' => 'albums/one/a.webp',
            'thumbnail_path' => 'albums/one/thumbs/a.webp',
        ])->delete();

        $this->assertSame([[
            Storage::disk('public')->url('albums/one/a.webp'),
            Storage::disk('public')->url('albums/one/thumbs/a.webp'),
        ]], $this->purgedBatches());
    }

    /**
     * Replacing a file purges the URL the old object was served at,
     * which the new upload's UUID name leaves behind untouched.
     */
    public function test_replacing_a_bulletin_purges_the_file_it_replaced(): void
    {
        $bulletin = Bulletin::create([
            'title' => '주보',
            'file_path' => 'bulletins/bulletin-2026-08-02.pdf',
            'published_at' => '2026-08-02',
        ]);

        $bulletin->update(['file_path' => 'bulletins/bulletin-2026-08-02-120000.pdf']);

        $this->assertSame(
            [[Storage::disk('public')->url('bulletins/bulletin-2026-08-02.pdf')]],
            $this->purgedBatches(),
        );
    }

    /**
     * Deleting a roster record takes the photograph off the bucket and
     * off the edge, so an unpublished member's picture really goes.
     */
    public function test_deleting_a_member_removes_and_purges_the_photo(): void
    {
        Storage::disk('public')->put('members/abc.webp', 'x');

        Member::create(['name' => '홍길동', 'photo' => 'members/abc.webp'])->delete();

        Storage::disk('public')->assertMissing('members/abc.webp');

        $this->assertSame(
            [[Storage::disk('public')->url('members/abc.webp')]],
            $this->purgedBatches(),
        );
    }

    /**
     * An album cascades to its photographs, and the whole cascade goes
     * out in batches of thirty - Cloudflare's per-call limit.
     */
    public function test_an_album_cascade_is_purged_in_batches_of_thirty(): void
    {
        $album = Album::factory()->create([
            'cover_photo_path' => 'albums/one/cover.webp',
            'cover_thumbnail_path' => 'albums/one/thumbs/cover.webp',
        ]);

        foreach (range(1, 20) as $index) {
            Photo::create([
                'album_id' => $album->id,
                'filename' => "{$index}.webp",
                'original_filename' => "{$index}.jpg",
                'path' => "albums/one/{$index}.webp",
                'thumbnail_path' => "albums/one/thumbs/{$index}.webp",
            ]);
        }

        $album->delete();

        $batches = $this->purgedBatches();

        $this->assertSame([30, 12], array_map('count', $batches));
        $this->assertContains(Storage::disk('public')->url('albums/one/cover.webp'), $batches[1]);
    }

    /**
     * Without a token nothing is called and nothing breaks - which is
     * every local and test environment, and production too if the API
     * token is ever rotated without the purge permission.
     */
    public function test_an_unconfigured_token_skips_the_purge_without_breaking_the_delete(): void
    {
        config()->set('services.cloudflare.api_token', null);

        $bulletin = Bulletin::create([
            'title' => '주보',
            'file_path' => 'bulletins/bulletin-2026-08-02.pdf',
            'published_at' => '2026-08-02',
        ]);

        $bulletin->delete();

        $this->assertSame([], $this->purgedBatches());
        $this->assertDatabaseCount('bulletins', 0);
    }

    /**
     * A refused or unreachable purge is logged and swallowed: losing
     * the CDN must never stop a family having a photograph taken down.
     */
    public function test_a_failed_purge_never_breaks_the_delete(): void
    {
        Http::fake(['api.cloudflare.com/*' => fn () => throw new ConnectionException('unreachable')]);

        $bulletin = Bulletin::create([
            'title' => '주보',
            'file_path' => 'bulletins/bulletin-2026-08-02.pdf',
            'published_at' => '2026-08-02',
        ]);

        $bulletin->delete();

        CloudflareCachePurger::flush();

        $this->assertDatabaseCount('bulletins', 0);
    }
}
