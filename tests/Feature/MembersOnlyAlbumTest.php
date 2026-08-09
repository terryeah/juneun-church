<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Database\Seeders\PositionSeeder;
use Database\Seeders\ServiceTypeSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers 성도 전용 앨범: photo sets published to signed-in 성도 only.
 *
 * Every assertion here is about what a guest's response contains, not
 * about what is hidden in the markup - a restricted album is excluded
 * from the query, so neither its title nor its slug is ever rendered.
 */
class MembersOnlyAlbumTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The restricted album under test.
     */
    private Album $restricted;

    /**
     * Seed the reference data the public pages rely on, then publish one
     * restricted album alongside an ordinary open one.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PositionSeeder::class,
            ServiceTypeSeeder::class,
            SiteSettingSeeder::class,
        ]);

        $this->restricted = Album::factory()->create([
            'title' => '2026 성도 수련회',
            'slug' => 'album-members-retreat',
            'is_members_only' => true,
        ]);
    }

    /**
     * A guest gets neither the title nor the slug on the album grid.
     */
    public function test_a_guest_does_not_receive_a_restricted_album_on_the_gallery(): void
    {
        $this->get('/gallery')
            ->assertOk()
            ->assertDontSee('2026 성도 수련회')
            ->assertDontSee('album-members-retreat');
    }

    /**
     * A guest hitting the detail URL directly gets a 404, not a 403: a
     * 403 would confirm the album exists at that slug.
     */
    public function test_a_guest_gets_a_404_on_the_detail_url(): void
    {
        $this->get('/gallery/album-members-retreat')
            ->assertNotFound()
            ->assertDontSee('2026 성도 수련회');
    }

    /**
     * A signed-in 성도 sees the album on the grid carrying the 성도 전용
     * badge, and may open it.
     */
    public function test_a_signed_in_member_sees_the_album_with_the_badge(): void
    {
        $badge = 'inline-flex items-center rounded-md border border-success bg-slate-900 px-2 py-0.5 align-middle font-kr text-xs font-medium text-success';

        $this->actingAs(User::factory()->create())
            ->get('/gallery')
            ->assertOk()
            ->assertSee('2026 성도 수련회')
            ->assertSee($badge, false)
            ->assertSee('성도 전용');

        $this->actingAs(User::factory()->create())
            ->get('/gallery/album-members-retreat')
            ->assertOk()
            ->assertSee('2026 성도 수련회');
    }

    /**
     * An ordinary published album still reaches a guest, badge-free.
     */
    public function test_an_open_album_still_reaches_a_guest(): void
    {
        Album::factory()->create([
            'title' => '여름 성경학교',
            'slug' => 'album-summer-school',
        ]);

        $this->get('/gallery')
            ->assertOk()
            ->assertSee('여름 성경학교')
            ->assertDontSee('성도 전용');

        $this->get('/gallery/album-summer-school')
            ->assertOk()
            ->assertSee('여름 성경학교');
    }

    /**
     * The sitemap never lists a restricted album, signed in or not,
     * because the document is written for crawlers and may be cached by
     * the CDN in front of the site.
     */
    public function test_the_sitemap_omits_a_restricted_album(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee(route('gallery.show', $this->restricted));

        $this->actingAs(User::factory()->create())
            ->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee(route('gallery.show', $this->restricted));
    }

    /**
     * The home slider is deliberately unaffected by the restriction: a
     * photograph hand-picked with 홈 슬라이더에 표시 keeps its place in the
     * band for a guest even when its album is 성도 전용. The flag is the
     * photographer saying this single image is fit for the front page,
     * which is a decision about the photograph, not about the album.
     */
    public function test_a_slider_photo_in_a_restricted_album_still_reaches_a_guest(): void
    {
        $photo = Photo::factory()->for($this->restricted)->create([
            'featured_in_slider' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee($photo->thumbnailUrl(), false);
    }

    /**
     * The tile still has to behave: showing the photograph is one thing,
     * but linking a guest to an album they cannot open would land them
     * on a 404, and naming it in the alt text would give away a title
     * the rest of the page withholds.
     */
    public function test_a_slider_tile_does_not_lead_a_guest_to_a_restricted_album(): void
    {
        Photo::factory()->for($this->restricted)->create(['featured_in_slider' => true]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee(route('gallery.show', $this->restricted))
            ->assertDontSee($this->restricted->title);
    }

    /**
     * Signed in, the same tile links through to the album itself.
     */
    public function test_a_slider_tile_leads_a_member_to_the_album(): void
    {
        Photo::factory()->for($this->restricted)->create(['featured_in_slider' => true]);

        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertOk()
            ->assertSee(route('gallery.show', $this->restricted));
    }
}
