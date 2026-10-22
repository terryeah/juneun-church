<?php

namespace Tests\Feature;

use App\Models\BibleTyping;
use App\Models\BibleVerse;
use App\Models\Cell;
use App\Models\Member;
use App\Models\User;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers 성경 타자: the verses are there, a verse counts only when it
 * is typed right, and the leaderboards add up.
 */
class BibleTypingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);
    }

    /**
     * The migration loaded the whole of 개역한글.
     */
    public function test_the_verses_were_loaded(): void
    {
        $this->assertSame(31101, BibleVerse::count());
        $this->assertSame('태초에 하나님이 천지를 창조하시니라', BibleVerse::query()->where(['book' => 1, 'chapter' => 1, 'verse' => 1])->value('text'));
        $this->assertSame(22, BibleVerse::query()->where('book', 66)->max('chapter'));
    }

    /**
     * A guest and an account off the 교적 get the notice, and cannot post.
     */
    public function test_the_page_is_for_the_roster_alone(): void
    {
        $first = BibleVerse::query()->orderBy('id')->first();

        $this->get('/typing')->assertOk()->assertSee('section-members-only');
        $this->get('/typing/write')->assertOk()->assertSee('section-members-only')->assertDontSee($first->text);
        $this->post('/typing/write', ['verse_id' => $first->id, 'body' => $first->text])->assertRedirect('/login');

        $this->actingAs(User::factory()->create());
        $this->get('/typing/write')->assertOk()->assertSee('section-members-only');
        $this->post('/typing/write', ['verse_id' => $first->id, 'body' => $first->text])->assertForbidden();

        $this->assertSame(0, BibleTyping::count());
    }

    /**
     * A verse typed right counts and the next one comes up; typed wrong,
     * it does not and the same one stays.
     */
    public function test_a_verse_counts_once_typed_right(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create());

        $this->get('/typing/write?book=1')->assertSee('창세기 1:1')->assertSee('태초에 하나님이 천지를 창조하시니라');

        $this->post('/typing/write', ['verse_id' => 1, 'body' => '태초에 하나님이 천지를 창조하시니라!'])
            ->assertRedirect('/typing/write?book=1')
            ->assertSessionHasErrors('body');
        $this->assertSame(0, BibleTyping::count());

        /** Spacing is forgiven. */
        $this->post('/typing/write', ['verse_id' => 1, 'body' => '태초에  하나님이 천지를창조하시니라 '])
            ->assertRedirect('/typing/write?book=1')
            ->assertSessionHas('status', '창세기 1:1 완료');
        $this->assertSame(1, BibleTyping::count());

        $this->get('/typing/write?book=1')->assertSee('창세기 1:2')->assertSee('1 / 1,533절');

        /** Typing it again does not count twice. */
        $this->post('/typing/write', ['verse_id' => 1, 'body' => '태초에 하나님이 천지를 창조하시니라']);
        $this->assertSame(1, BibleTyping::count());

        $this->get('/typing')->assertSee('이번 달 1절 · 지금까지 1절');
    }

    /**
     * The leaderboards count people and their 셀.
     */
    public function test_the_leaderboards_add_up(): void
    {
        $cell = Cell::query()->create(['name' => '1셀']);
        $busy = User::factory()->onTheRoster()->create(['name' => '김부지런']);
        $idle = User::factory()->onTheRoster()->create(['name' => '이느긋']);
        Member::query()->whereIn('user_id', [$busy->getKey(), $idle->getKey()])->update(['cell_id' => $cell->getKey()]);

        foreach ([1, 2, 3] as $verse) {
            BibleTyping::create(['user_id' => $busy->getKey(), 'bible_verse_id' => $verse]);
        }
        BibleTyping::create(['user_id' => $idle->getKey(), 'bible_verse_id' => 1]);

        $this->actingAs($idle)
            ->get('/typing')
            ->assertSeeInOrder(['김부지런', '3절', '이느긋', '1절'])
            ->assertSee('4절');

        $this->assertStringContainsString('1셀', $this->get('/typing')->getContent());
    }

    /**
     * A book that is not one of the 66 is not a page.
     */
    public function test_an_unknown_book_is_not_found(): void
    {
        $this->actingAs(User::factory()->onTheRoster()->create())
            ->get('/typing/write?book=67')
            ->assertNotFound();
    }
}
