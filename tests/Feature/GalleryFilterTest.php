<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\User;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the audience filter on 갤러리.
 *
 * A signed-in 성도 sees both kinds of album and can narrow the grid to
 * one or the other. A guest is offered no chips, because everything
 * they can reach is open already, and the filter never widens what
 * scopeVisible allows.
 */
class GalleryFilterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * One album of each kind, both published.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);

        Album::factory()->create([
            'title' => '성도 수련회',
            'slug' => 'album-retreat',
            'is_members_only' => true,
        ]);

        Album::factory()->create([
            'title' => '성탄 감사예배',
            'slug' => 'album-christmas',
            'is_members_only' => false,
        ]);
    }

    /**
     * A guest is offered no chips at all.
     */
    public function test_a_guest_is_offered_no_filter(): void
    {
        $this->get('/gallery')
            ->assertOk()
            ->assertDontSee('data-gallery-chip', false)
            ->assertSee('성탄 감사예배')
            ->assertDontSee('성도 수련회');
    }

    /**
     * A signed-in 성도 gets the chips and, by default, everything.
     */
    public function test_a_member_sees_the_filter_and_every_album(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/gallery')
            ->assertOk()
            ->assertSee('data-gallery-chip', false)
            ->assertSee('성도 수련회')
            ->assertSee('성탄 감사예배');
    }

    /**
     * 성도 전용 narrows the grid to the restricted albums.
     */
    public function test_the_members_filter_narrows_the_grid(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/gallery?visibility=members')
            ->assertOk()
            ->assertSee('성도 수련회')
            ->assertDontSee('성탄 감사예배');
    }

    /**
     * 모두 공개 narrows it the other way.
     */
    public function test_the_public_filter_narrows_the_grid(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/gallery?visibility=public')
            ->assertOk()
            ->assertSee('성탄 감사예배')
            ->assertDontSee('성도 수련회');
    }

    /**
     * The filter narrows what is visible; it never widens it. A guest
     * asking for the restricted albums by hand still gets none.
     */
    public function test_the_filter_never_widens_what_a_guest_may_see(): void
    {
        $this->get('/gallery?visibility=members')
            ->assertOk()
            ->assertDontSee('성도 수련회')
            ->assertDontSee('album-retreat');
    }

    /**
     * An unknown value falls back to showing everything rather than
     * erroring or quietly emptying the page.
     */
    public function test_an_unknown_filter_falls_back_to_everything(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/gallery?visibility=nonsense')
            ->assertOk()
            ->assertSee('성도 수련회')
            ->assertSee('성탄 감사예배');
    }
}
