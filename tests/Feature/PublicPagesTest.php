<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\Photo;
use Database\Seeders\PositionSeeder;
use Database\Seeders\ServiceTypeSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Smoke tests covering every public page of the site.
 */
class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the reference data the layout and pages rely on.
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
     * Every public route responds successfully.
     */
    public function test_all_public_pages_return_a_successful_response(): void
    {
        $routes = [
            '/', '/worship', '/news', '/events', '/people',
            '/downloads', '/album', '/giving', '/location',
        ];

        foreach ($routes as $route) {
            $this->get($route)->assertStatus(200);
        }
    }

    /**
     * The worship page lists every service on the current timetable.
     */
    public function test_worship_page_lists_every_service(): void
    {
        $response = $this->get('/worship')->assertStatus(200);

        $services = [
            '주일 1부 예배 (사역자 예배)',
            '주일 2부 예배 (청장년부)',
            '수요기도회',
            '유초등부',
            '청소년부',
        ];

        foreach ($services as $service) {
            $response->assertSee($service);
        }
    }

    /**
     * Unpublished announcements are hidden from the public site.
     */
    public function test_unpublished_announcements_are_not_visible(): void
    {
        $announcement = Announcement::factory()->draft()->create();

        $this->get('/news/'.$announcement->slug)->assertStatus(404);
        $this->get('/news')->assertDontSee($announcement->title);
    }

    /**
     * Published announcements are visible on their detail page.
     */
    public function test_published_announcements_are_visible(): void
    {
        $announcement = Announcement::factory()->create();

        $this->get('/news/'.$announcement->slug)
            ->assertStatus(200)
            ->assertSee($announcement->title);
    }

    /**
     * The moments band ships four fetchable photos and defers the rest.
     *
     * Every slide the initial HTML resolves is a photograph the browser
     * downloads before the hero image is done, so the count is the whole
     * point: four fill the widest first view, and the remainder must sit
     * inside the inert template where nothing requests them.
     */
    public function test_home_moments_band_defers_all_but_the_first_four_photos(): void
    {
        Photo::factory()->count(10)->for(Album::factory()->create())->create();

        $home = (string) $this->get('/')->assertStatus(200)->getContent();

        $track = Str::before(Str::after($home, 'data-slider-track'), '</div>');
        $deferred = Str::before(Str::after($home, 'data-slider-deferred'), '</template>');

        $this->assertSame(4, substr_count($track, '<img src="'));
        $this->assertSame(6, substr_count($deferred, '<img src="'));
    }

    /**
     * A link shared in a chat carries the church's name and a picture.
     *
     * The card title used to be the page title alone, so 예배 안내
     * arrived with nothing to say whose it was, and most pages passed
     * no image at all - which is the card nobody looks at twice, on the
     * route most people actually arrive by.
     */
    public function test_a_shared_link_names_the_church_and_carries_a_picture(): void
    {
        $this->get('/worship')
            ->assertOk()
            ->assertSee('<meta property="og:title" content="예배 안내 · '.config('app.name').'">', false)
            ->assertSee('<meta property="og:image"', false);

        /** The home page has no title of its own, so the name stands alone. */
        $this->get('/')
            ->assertOk()
            ->assertSee('<meta property="og:title" content="'.config('app.name').'">', false);
    }

    /**
     * A page with nothing on it today still says what it is for.
     *
     * '예정된 행사가 없습니다' on its own is a dead end for a reader and
     * an empty page to a search engine, and 교회 행사 sits in the main
     * navigation.
     */
    public function test_an_empty_events_page_still_says_what_is_on(): void
    {
        Event::query()->delete();

        $this->get('/events')
            ->assertOk()
            ->assertSee('매주 모이는 예배와 모임은 계속됩니다');
    }
}
