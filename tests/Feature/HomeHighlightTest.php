<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Photo;
use App\Models\SiteSetting;
use Database\Seeders\PositionSeeder;
use Database\Seeders\ServiceTypeSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Covers the home 하이라이트 picture, which must survive an admin
 * replacing the image with an upload under a different filename.
 */
class HomeHighlightTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the reference data the home page relies on.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PositionSeeder::class,
            ServiceTypeSeeder::class,
            SiteSettingSeeder::class,
        ]);
    }

    /**
     * The 주는마켓 migration leaves the highlight pointing at its album
     * with no filename, ready for the poster to be uploaded.
     */
    public function test_the_migration_points_the_highlight_at_the_album(): void
    {
        $this->assertSame('', SiteSetting::get('highlight_photo'));
        $this->assertSame('giving-market', SiteSetting::get('highlight_link_album'));
        $this->assertStringContainsString('주는마켓', (string) SiteSetting::get('highlight_title'));
        $this->assertTrue(Album::query()->where('slug', 'giving-market')->exists());
    }

    /**
     * With no photo in the album the section drops the picture column
     * rather than showing an empty frame.
     */
    public function test_the_section_renders_without_a_photo(): void
    {
        $section = $this->highlightSection();

        $this->assertStringContainsString('주는마켓', $section);
        $this->assertStringNotContainsString('<img', $section);
        $this->assertStringNotContainsString('lg:grid-cols-[1.3fr_1fr]', $section);
    }

    /**
     * A photo uploaded into the linked album is picked up even though
     * no filename was ever entered in 사이트 설정.
     */
    public function test_the_first_album_photo_stands_in_for_a_missing_filename(): void
    {
        $album = Album::query()->where('slug', 'giving-market')->sole();

        $second = Photo::factory()->for($album)->create(['sort_order' => 2, 'thumbnail_path' => 'thumbs/second.webp']);
        $first = Photo::factory()->for($album)->create(['sort_order' => 1, 'thumbnail_path' => 'thumbs/first.webp']);

        $section = $this->highlightSection();

        $this->assertStringContainsString('lg:grid-cols-[1.3fr_1fr]', $section);
        $this->assertStringContainsString($first->thumbnailUrl(), $section);
        $this->assertStringNotContainsString($second->thumbnailUrl(), $section);
    }

    /**
     * A filename that still resolves keeps precedence over the album.
     */
    public function test_a_resolvable_filename_still_wins(): void
    {
        $album = Album::query()->where('slug', 'giving-market')->sole();

        $chosen = Photo::factory()->for($album)->create(['sort_order' => 9, 'thumbnail_path' => 'thumbs/chosen.webp']);
        $ignored = Photo::factory()->for($album)->create(['sort_order' => 1, 'thumbnail_path' => 'thumbs/other.webp']);

        SiteSetting::query()->where('key', 'highlight_photo')->sole()->update(['value' => $chosen->filename]);

        $section = $this->highlightSection();

        $this->assertStringContainsString($chosen->thumbnailUrl(), $section);
        $this->assertStringNotContainsString($ignored->thumbnailUrl(), $section);
    }

    /**
     * The rendered 하이라이트 section only, so the gallery slider below
     * it cannot satisfy an assertion meant for the highlight picture.
     */
    private function highlightSection(): string
    {
        $home = $this->get('/')->assertOk()->getContent();

        return Str::between((string) $home, 'section-highlight', 'section-moments-intro');
    }
}
