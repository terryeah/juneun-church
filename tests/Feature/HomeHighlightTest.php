<?php

namespace Tests\Feature;

use App\Models\Announcement;
use Database\Seeders\PositionSeeder;
use Database\Seeders\ServiceTypeSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Covers the home 하이라이트 section, which is now a property of a
 * 교회 소식 rather than a set of loose 사이트 설정 values.
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
     * The migration carries the 주는마켓 notice over as a published,
     * highlighted announcement so the section survives the deploy.
     */
    public function test_the_migration_converts_the_giving_market_notice(): void
    {
        $announcement = Announcement::query()->where('slug', 'news-20260806')->sole();

        $this->assertTrue($announcement->is_highlighted);
        $this->assertTrue($announcement->is_published);
        $this->assertFalse($announcement->is_pinned);
        $this->assertStringContainsString('주는마켓', $announcement->title);
        $this->assertSame('albums/giving-market/giving-market-1.webp', $announcement->featured_image);
    }

    /**
     * The picture and the heading both open the announcement itself.
     */
    public function test_the_section_links_the_highlight_to_its_announcement(): void
    {
        $announcement = $this->highlight(['featured_image' => 'announcements/poster.webp']);

        $section = $this->highlightSection();
        $url = route('news.show', $announcement);

        $this->assertStringContainsString($announcement->title, $section);

        /** The picture, the heading and the 자세히 보기 link all point at it */
        $this->assertSame(3, substr_count($section, 'href="'.$url.'"'));

        $this->assertStringContainsString(
            Storage::disk(config('filesystems.media'))->url('announcements/poster.webp'),
            $section,
        );
        $this->assertStringContainsString('lg:grid-cols-[1.3fr_1fr]', $section);
    }

    /**
     * The body copy is a plain-text excerpt of the rich editor content,
     * with no markup leaking into the page.
     */
    public function test_the_section_shows_a_plain_text_excerpt(): void
    {
        $this->highlight(['content' => '<p>첫째 문단입니다.</p><p>둘째 문단입니다.</p>']);

        $section = $this->highlightSection();

        $this->assertStringContainsString('첫째 문단입니다. 둘째 문단입니다.', $section);
        $this->assertStringNotContainsString('&lt;p&gt;', $section);
    }

    /**
     * Without a 대표 이미지 the section drops the picture column rather
     * than showing an empty frame.
     */
    public function test_the_section_renders_without_a_featured_image(): void
    {
        $announcement = $this->highlight(['featured_image' => null]);

        $section = $this->highlightSection();

        $this->assertStringContainsString($announcement->title, $section);
        $this->assertStringNotContainsString('<img', $section);
        $this->assertStringNotContainsString('lg:grid-cols-[1.3fr_1fr]', $section);
    }

    /**
     * With nothing flagged the whole section disappears.
     */
    public function test_the_section_is_absent_when_nothing_is_highlighted(): void
    {
        Announcement::query()->update(['is_highlighted' => false]);

        $this->assertStringNotContainsString('section-highlight', (string) $this->get('/')->assertOk()->getContent());
    }

    /**
     * An unpublished or expired notice never reaches the home page,
     * even while it holds the flag.
     */
    public function test_an_unpublished_highlight_is_not_shown(): void
    {
        $this->highlight(['is_published' => false]);

        $this->assertStringNotContainsString('section-highlight', (string) $this->get('/')->assertOk()->getContent());
    }

    /**
     * The flag is exclusive: whoever is saved with it on takes it from
     * whoever held it, whatever the save came through.
     */
    public function test_only_one_announcement_holds_the_highlight(): void
    {
        $first = $this->highlight();
        $second = Announcement::factory()->create(['is_highlighted' => true]);

        $this->assertFalse($first->fresh()->is_highlighted);
        $this->assertTrue($second->fresh()->is_highlighted);
        $this->assertSame(1, Announcement::query()->where('is_highlighted', true)->count());

        $third = Announcement::factory()->create();
        $third->update(['is_highlighted' => true]);

        $this->assertFalse($second->fresh()->is_highlighted);
        $this->assertSame(1, Announcement::query()->where('is_highlighted', true)->count());
    }

    /**
     * Create a highlighted announcement, displacing the one the
     * migration installed.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function highlight(array $attributes = []): Announcement
    {
        return Announcement::factory()->create([...$attributes, 'is_highlighted' => true]);
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
