<?php

namespace Tests\Feature;

use App\Models\Devotion;
use App\Models\DevotionNote;
use App\Models\User;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers 큐티: 성도 전용 as a page, and each 묵상 readable by its
 * writer alone.
 */
class DevotionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Today's day under test.
     */
    private Devotion $today;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);

        $this->today = Devotion::factory()->create(['title' => '은혜를 보는 사람']);
    }

    /**
     * A guest gets the sign-in notice and nothing of the day.
     */
    public function test_a_guest_gets_the_sign_in_notice(): void
    {
        foreach (['/qt', '/qt/notes', '/qt/'.$this->today->id] as $page) {
            $this->get($page)
                ->assertOk()
                ->assertSee('section-members-only')
                ->assertDontSee('은혜를 보는 사람');
        }

        $this->post('/qt/'.$this->today->id.'/note', ['body' => '묵상'])->assertRedirect('/login');
        $this->assertSame(0, DevotionNote::count());
    }

    /**
     * A signed-in account off the 교적 cannot write either.
     */
    public function test_an_account_off_the_roster_cannot_write(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/qt/'.$this->today->id.'/note', ['body' => '묵상'])
            ->assertForbidden();
    }

    /**
     * A 성도 writes a 묵상, sees it again, and another 성도 does not.
     */
    public function test_a_note_is_seen_by_its_writer_alone(): void
    {
        $writer = User::factory()->onTheRoster()->create();
        $other = User::factory()->onTheRoster()->create();

        $this->actingAs($writer)
            ->post('/qt/'.$this->today->id.'/note', ['body' => '부족함 속에서 은혜를 보았다'])
            ->assertRedirect('/qt/'.$this->today->id);

        $this->get('/qt/'.$this->today->id)->assertSee('부족함 속에서 은혜를 보았다');
        $this->get('/qt/notes')->assertSee('부족함 속에서 은혜를 보았다');
        $this->get('/qt')->assertSee('✓ 묵상함');

        $this->actingAs($other);
        $this->get('/qt/'.$this->today->id)->assertOk()->assertDontSee('부족함 속에서 은혜를 보았다');
        $this->get('/qt/notes')->assertDontSee('부족함 속에서 은혜를 보았다');
        $this->get('/qt')->assertDontSee('✓ 묵상함');
    }

    /**
     * Saving again replaces the 묵상, and saving it empty removes it.
     */
    public function test_saving_replaces_and_emptying_removes(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create());

        $this->post('/qt/'.$this->today->id.'/note', ['body' => '첫 묵상']);
        $this->post('/qt/'.$this->today->id.'/note', ['body' => '고친 묵상']);

        $this->assertSame(['고친 묵상'], DevotionNote::pluck('body')->all());

        $this->post('/qt/'.$this->today->id.'/note', ['body' => '  ']);

        $this->assertSame(0, DevotionNote::count());
    }

    /**
     * A day prepared ahead, or left unpublished, is not out yet.
     */
    public function test_a_day_ahead_or_unpublished_stays_hidden(): void
    {
        $tomorrow = Devotion::factory()->create(['devotion_date' => today()->addDay(), 'title' => '내일의 큐티']);
        $draft = Devotion::factory()->create(['devotion_date' => today()->subDay(), 'title' => '준비 중인 큐티', 'is_published' => false]);

        $this->actingAs(User::factory()->onTheRoster()->create());

        $this->get('/qt')->assertSee('은혜를 보는 사람')->assertDontSee('내일의 큐티')->assertDontSee('준비 중인 큐티');
        $this->get('/qt/'.$tomorrow->id)->assertNotFound();
        $this->get('/qt/'.$draft->id)->assertNotFound();
        $this->post('/qt/'.$tomorrow->id.'/note', ['body' => '묵상'])->assertNotFound();
    }
}
