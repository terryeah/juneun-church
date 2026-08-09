<?php

namespace Tests\Feature;

use App\Models\Offering;
use App\Models\User;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the giving page (헌금): week selection and the login gate on
 * the weekly offering records.
 */
class GivingPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the settings the page renders plus two weeks of offerings.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);

        /** A migration seeds a real 2026-07-19 offering, so start from a known set. */
        Offering::query()->delete();

        Offering::create([
            'sunday_date' => '2026-07-19',
            'items' => [['category' => '십일조', 'name' => '김철수', 'amount' => '111.00']],
        ]);

        Offering::create([
            'sunday_date' => '2026-07-26',
            'items' => [['category' => '십일조', 'name' => '이영희', 'amount' => '222.00']],
        ]);
    }

    /**
     * Without a week parameter the most recent Sunday is rendered.
     */
    public function test_the_latest_week_is_shown_by_default(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/giving')
            ->assertOk()
            ->assertSee('이영희')
            ->assertDontSee('김철수');
    }

    /**
     * Requesting an earlier week renders that week's figures only.
     */
    public function test_a_requested_week_is_shown_instead_of_the_latest(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/giving?week=2026-07-19')
            ->assertOk()
            ->assertSee('2026년 7월 19일 주일 헌금 내역')
            ->assertSee('김철수')
            ->assertSee('$111.00')
            ->assertDontSee('이영희')
            ->assertDontSee('$222.00');
    }

    /**
     * An unknown week falls back to the most recent Sunday.
     */
    public function test_an_unknown_week_falls_back_to_the_latest(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/giving?week=1999-01-03')
            ->assertOk()
            ->assertSee('이영희');
    }

    /**
     * Guests receive the bank details but never the records markup.
     */
    public function test_guests_do_not_receive_the_records_section(): void
    {
        $this->get('/giving')
            ->assertOk()
            ->assertSee('이체 시 참조란에 이름과 헌금 종류를 약자로 함께 적어주세요.', false)
            ->assertDontSee('section-giving-records')
            ->assertDontSee('지난 주일 보기')
            ->assertDontSee('이영희');
    }

    /**
     * Signed-in members see the records behind their login badge.
     */
    public function test_members_receive_the_records_section(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/giving')
            ->assertOk()
            ->assertSee('section-giving-records')
            ->assertSee('성도 전용');
    }
}
