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
        $this->get('/album')
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
        $this->get('/album/album-members-retreat')
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

        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/album')
            ->assertOk()
            ->assertSee('2026 성도 수련회')
            ->assertSee($badge, false)
            ->assertSee('성도 전용');

        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/album/album-members-retreat')
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

        $this->get('/album')
            ->assertOk()
            ->assertSee('여름 성경학교')
            ->assertDontSee('성도 전용');

        $this->get('/album/album-summer-school')
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
            ->assertDontSee(route('album.show', $this->restricted));

        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee(route('album.show', $this->restricted));
    }

    /**
     * The home slider never draws on a 성도 전용 album, and picking a
     * photograph by hand does not override that. The front page is the
     * one screen a stranger always sees.
     */
    public function test_the_slider_leaves_a_restricted_album_alone(): void
    {
        $photo = Photo::factory()->for($this->restricted)->create([
            'featured_in_slider' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee($photo->thumbnailUrl(), false)
            ->assertDontSee(route('album.show', $this->restricted))
            ->assertDontSee($this->restricted->title);
    }

    /**
     * Signing in does not change it either. The band is the same for
     * everyone, so a 성도 전용 photograph cannot reach it by any route.
     */
    public function test_the_slider_leaves_it_alone_for_a_member_too(): void
    {
        $photo = Photo::factory()->for($this->restricted)->create([
            'featured_in_slider' => true,
        ]);

        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/')
            ->assertOk()
            ->assertDontSee($photo->thumbnailUrl(), false);
    }

    /**
     * An open album still fills the band as it always did.
     */
    public function test_the_slider_still_draws_on_an_open_album(): void
    {
        $open = Album::factory()->create([
            'title' => '전교인 나들이',
            'slug' => 'album-outing',
            'is_members_only' => false,
        ]);

        $photo = Photo::factory()->for($open)->create(['featured_in_slider' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSee($photo->thumbnailUrl(), false)
            ->assertSee(route('album.show', $open));
    }

    /**
     * The album page states how many photos the album holds, not how
     * many fitted on the first page.
     *
     * The grid renders 24 at a time and the lightbox fetches the rest
     * as it reaches them, so it counts from this figure. Without it the
     * lightbox would announce '사진 1 / 24' in an album of 60, which
     * reads as though the album ended at the first screenful - which is
     * exactly how it behaved before it could ask for more.
     */
    public function test_the_album_page_states_the_whole_photo_count(): void
    {
        $album = Album::factory()->create([
            'title' => '수련회',
            'slug' => 'album-camp',
            'is_members_only' => false,
        ]);

        Photo::factory()->for($album)->count(30)->create();

        $this->get(route('album.show', $album))
            ->assertOk()
            ->assertSee('data-photo-total="30"', false);
    }
}
