<?php

namespace Tests\Feature;

use App\Models\Bulletin;
use App\Models\User;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers 성도 전용 주보.
 *
 * A bulletin carries cell assignments, the names of those serving and
 * the offering record, so it is restricted the way notices naming
 * members already are. As there, the assertions are about what reaches
 * a guest's response: a restricted bulletin leaves the query, so
 * neither its title nor the URL of its PDF is ever rendered.
 */
class MembersOnlyBulletinTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The restricted bulletin under test.
     */
    private Bulletin $restricted;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);

        $this->restricted = Bulletin::factory()->create([
            'title' => '주일 예배 주보',
            'file_path' => 'bulletins/bulletin-2026-08-09.pdf',
            'published_at' => '2026-08-09',
            'is_members_only' => true,
        ]);
    }

    /**
     * The column defaults to restricted, so a bulletin uploaded without
     * a thought is closed rather than open.
     */
    public function test_a_bulletin_is_restricted_by_default(): void
    {
        $bulletin = Bulletin::create([
            'title' => '기본값 확인',
            'file_path' => 'bulletins/default.pdf',
            'published_at' => '2026-08-16',
        ]);

        $this->assertTrue($bulletin->fresh()->is_members_only);
    }

    /**
     * A guest gets neither the title nor the path to the PDF.
     */
    public function test_a_guest_does_not_receive_a_restricted_bulletin(): void
    {
        $this->get('/bulletins')
            ->assertOk()
            ->assertDontSee('bulletin-2026-08-09.pdf')
            ->assertSee('주보는 로그인 후 보실 수 있습니다')
            ->assertDontSee('등록된 주보가 없습니다.');
    }

    /**
     * A signed-in 성도 sees it.
     */
    public function test_a_signed_in_member_sees_the_bulletin(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/bulletins')
            ->assertOk()
            ->assertSee('주일 예배 주보')
            ->assertSee('bulletin-2026-08-09.pdf')
            ->assertDontSee('주보는 로그인 후 보실 수 있습니다');
    }

    /**
     * A bulletin the church opens deliberately reaches a guest, and
     * with nothing held back the sign-in offer stays away rather than
     * inviting them to log in for something they can already read.
     */
    public function test_an_open_bulletin_reaches_a_guest_without_the_prompt(): void
    {
        $this->restricted->update(['is_members_only' => false]);

        $this->get('/bulletins')
            ->assertOk()
            ->assertSee('주일 예배 주보')
            ->assertDontSee('주보는 로그인 후 보실 수 있습니다');
    }
}
