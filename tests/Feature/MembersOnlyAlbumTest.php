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
 * Covers 앨범, which is published to 성도 on the 교적 only.
 *
 * The section is closed as a whole now rather than album by album.
 * Every assertion here is about what a guest's response contains, not
 * about what is hidden in the markup - the controller never runs the
 * query, so no title and no photograph URL is ever rendered.
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
     * A guest hitting the detail URL directly gets the section's own
     * heading and the login offer, never the album's title - which the
     * slug carries and which is the thing being kept back.
     */
    public function test_a_guest_gets_the_sign_in_notice_on_the_detail_url(): void
    {
        $this->get('/album/album-members-retreat')
            ->assertOk()
            ->assertSee('section-members-only')
            ->assertDontSee('2026 성도 수련회');
    }

    /**
     * A signed-in 성도 sees the album on the grid and may open it, and
     * no 성도 전용 tag rides on it: the whole page is one.
     */
    public function test_a_signed_in_member_sees_the_album_without_a_tag(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/album')
            ->assertOk()
            ->assertSee('2026 성도 수련회')
            ->assertDontSee('성도 전용');

        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/album/album-members-retreat')
            ->assertOk()
            ->assertSee('2026 성도 수련회');
    }

    /**
     * An album nobody marked restricted is no longer open either: the
     * page is closed, so the column on the record decides nothing.
     */
    public function test_an_unmarked_album_is_closed_with_the_rest(): void
    {
        Album::factory()->create([
            'title' => '여름 성경학교',
            'slug' => 'album-summer-school',
        ]);

        $this->get('/album')
            ->assertOk()
            ->assertDontSee('여름 성경학교')
            ->assertSee('section-members-only');

        $this->get('/album/album-summer-school')
            ->assertOk()
            ->assertDontSee('여름 성경학교');

        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/album/album-summer-school')
            ->assertOk()
            ->assertSee('여름 성경학교');
    }

    /**
     * The sitemap lists no album at all, and not the section either:
     * 앨범 answers a crawler with a login notice carrying noindex.
     */
    public function test_the_sitemap_omits_the_albums_and_the_section(): void
    {
        foreach ([null, User::factory()->onTheRoster()->create()] as $reader) {
            if ($reader) {
                $this->actingAs($reader);
            }

            $this->get('/sitemap.xml')
                ->assertOk()
                ->assertDontSee(route('album.show', $this->restricted))
                ->assertDontSee(route('album.index'));
        }
    }

    /**
     * The home slider goes by the box beside the photograph, not by the
     * album's audience.
     *
     * 앨범 is a 성도 전용 page now, so the album's own flag says nothing
     * about the front page. An admin ticking 홈 슬라이더에 표시 is the
     * decision that this one picture may be seen by anyone.
     */
    public function test_a_hand_picked_photo_reaches_the_slider_from_any_album(): void
    {
        $photo = Photo::factory()->for($this->restricted)->create([
            'featured_in_slider' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee($photo->thumbnailUrl(), false);
    }

    /**
     * A photograph nobody pinned never reaches the front page.
     *
     * The band used to top itself up from every published album when too
     * few were pinned. Most of the church's photographs sit in albums
     * kept to the 교적, so that filler put faces on a public page that
     * nobody had chosen to put there.
     */
    public function test_a_photo_nobody_picked_stays_off_the_slider(): void
    {
        $photo = Photo::factory()->for($this->restricted)->create();

        $this->get('/')
            ->assertOk()
            ->assertDontSee($photo->thumbnailUrl(), false);
    }

    /**
     * The band is the same for everyone, signed in or not.
     */
    public function test_the_slider_is_the_same_for_a_member(): void
    {
        $photo = Photo::factory()->for($this->restricted)->create([
            'featured_in_slider' => true,
        ]);

        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/')
            ->assertOk()
            ->assertSee($photo->thumbnailUrl(), false);
    }

    /**
     * A photograph from an album that is not listed still reaches the
     * band, but carries no link.
     *
     * 활성화 decides whether an album appears on 앨범, which is a
     * different question from whether one picture may be on the front
     * page - six albums are kept off that page while holding pictures
     * the site uses elsewhere. Its detail page 404s for everybody, a
     * 성도 included, so the slide is drawn without an anchor.
     */
    public function test_a_photo_from_an_unlisted_album_is_drawn_without_a_link(): void
    {
        $draft = Album::factory()->create([
            'title' => '사이트 자료',
            'slug' => 'album-assets',
            'is_published' => false,
        ]);

        $photo = Photo::factory()->for($draft)->create(['featured_in_slider' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSee($photo->thumbnailUrl(), false)
            ->assertDontSee(route('album.show', $draft));
    }

    /**
     * An open album still fills the band as it always did.
     */
    public function test_the_slider_still_draws_on_an_open_album(): void
    {
        $open = Album::factory()->create([
            'title' => '전교인 나들이',
            'slug' => 'album-outing',
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
        ]);

        Photo::factory()->for($album)->count(30)->create();

        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get(route('album.show', $album))
            ->assertOk()
            ->assertSee('data-photo-total="30"', false);
    }
}
