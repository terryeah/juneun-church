<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\User;
use App\Models\Video;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers 앨범 · 동영상 on the public site.
 *
 * The videos are unlisted on YouTube - anyone holding the identifier
 * can watch them - so most of this is about the identifier never
 * reaching a response it should not be in.
 */
class VideoAlbumPageTest extends TestCase
{
    use RefreshDatabase;

    private Album $album;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);

        $this->album = Album::factory()->create([
            'title' => '테스트 청소년부',
            'slug' => 'video-test-youth',
            'type' => Album::TYPE_VIDEO,
            'is_published' => true,
            'is_members_only' => true,
        ]);

        Video::factory()->for($this->album)->create([
            'youtube_id' => '556vWaIbHSE',
            'title' => '겨울 리트릿',
        ]);
    }

    /**
     * A 성도 sees the album, opens it, and gets the player's address.
     */
    public function test_a_member_reaches_the_videos(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create());

        $this->get('/album?kind=video')
            ->assertOk()
            ->assertSee('테스트 청소년부')
            ->assertSee('성도 전용');

        $this->get('/album/video-test-youth')
            ->assertOk()
            ->assertSee('겨울 리트릿')
            ->assertSee('youtube-nocookie.com/embed/556vWaIbHSE', false);
    }

    /**
     * A guest gets neither the album nor, above all, the identifier.
     *
     * The identifier is the video: anyone holding it can watch, so it
     * leaking is the whole of the harm. This asserts on the eleven
     * characters themselves rather than on the title.
     */
    public function test_the_identifier_never_reaches_a_guest(): void
    {
        $this->get('/album?kind=video')
            ->assertOk()
            ->assertDontSee('테스트 청소년부')
            ->assertDontSee('556vWaIbHSE');

        $this->get('/album/video-test-youth')
            ->assertNotFound()
            ->assertDontSee('556vWaIbHSE');

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee('video-test-youth');
    }

    /**
     * Nor a signed-in 일반회원, who is not on the 교적.
     */
    public function test_the_identifier_never_reaches_a_general_member(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/album?kind=video')->assertOk()->assertDontSee('556vWaIbHSE');
        $this->get('/album/video-test-youth')->assertNotFound();
    }

    /**
     * The two kinds do not spill into each other's page.
     */
    public function test_each_kind_lists_only_its_own_albums(): void
    {
        Album::factory()->create([
            'title' => '여름 성경학교 사진',
            'slug' => 'album-summer',
            'type' => Album::TYPE_PHOTO,
            'is_published' => true,
            'is_members_only' => false,
        ]);

        $this->actingAs(User::factory()->onTheRoster()->create());

        $this->get('/album')
            ->assertOk()
            ->assertSee('여름 성경학교 사진')
            ->assertDontSee('테스트 청소년부');

        $this->get('/album?kind=video')
            ->assertOk()
            ->assertSee('테스트 청소년부')
            ->assertDontSee('여름 성경학교 사진');
    }

    /**
     * An unknown kind falls back to the photographs rather than
     * erroring or showing everything.
     */
    public function test_an_unknown_kind_falls_back(): void
    {
        $this->get('/album?kind=nonsense')
            ->assertOk()
            ->assertSee('사진');
    }

    /**
     * The old gallery addresses still lead somewhere, permanently.
     */
    public function test_the_old_addresses_still_lead_to_the_new_ones(): void
    {
        $open = Album::factory()->create([
            'title' => '나들이',
            'slug' => 'album-outing',
            'is_published' => true,
            'is_members_only' => false,
        ]);

        $this->get('/gallery')->assertMovedPermanently()->assertRedirect('/album');
        $this->get('/gallery/album-outing')->assertMovedPermanently()->assertRedirect(route('album.show', $open));
    }

    /**
     * No YouTube code is loaded by the album page itself. The frame is
     * written in when somebody presses play, so a page nobody plays
     * anything on talks to YouTube only for the still images.
     */
    public function test_the_page_embeds_no_player_until_asked(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/album/video-test-youth')
            ->assertOk()
            ->assertDontSee('<iframe', false);
    }
}
