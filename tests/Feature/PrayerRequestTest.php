<?php

namespace Tests\Feature;

use App\Models\Cell;
use App\Models\Member;
use App\Models\Position;
use App\Models\PrayerRequest;
use App\Models\User;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers 기도제목: who sees a request shared with a 셀, with the church,
 * or with the 목회자 alone.
 */
class PrayerRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);
    }

    /**
     * A 성도 in the given 셀, or in none.
     */
    private function member(?Cell $cell = null, ?string $position = null): User
    {
        $user = User::factory()->onTheRoster()->create();

        Member::query()->where('user_id', $user->getKey())->update([
            'cell_id' => $cell?->getKey(),
            'position_id' => $position ? Position::query()->firstOrCreate(['name' => $position], ['sort_order' => 10])->getKey() : null,
        ]);

        return $user->fresh();
    }

    /**
     * A guest and an account off the 교적 get the notice, and cannot post.
     */
    public function test_the_page_is_for_the_roster_alone(): void
    {
        $this->get('/prayer')->assertOk()->assertSee('section-members-only');
        $this->post('/prayer', ['scope' => 'church', 'body' => '기도'])->assertRedirect('/login');

        $this->actingAs(User::factory()->create());
        $this->get('/prayer')->assertOk()->assertSee('section-members-only');
        $this->post('/prayer', ['scope' => 'church', 'body' => '기도'])->assertForbidden();

        $this->assertSame(0, PrayerRequest::count());
    }

    /**
     * A 셀 request reaches the 셀 and the 목회자, and nobody else.
     */
    public function test_a_cell_request_stays_in_the_cell(): void
    {
        $cell = Cell::query()->create(['name' => '1셀']);
        $writer = $this->member($cell);
        $cellMate = $this->member($cell);
        $outsider = $this->member(Cell::query()->create(['name' => '2셀']));
        $pastor = $this->member(null, '담임목사');

        $this->actingAs($writer)
            ->post('/prayer', ['scope' => 'cell', 'body' => '어머니 수술을 위해'])
            ->assertRedirect('/prayer?tab=cell');

        $this->actingAs($cellMate)->get('/prayer?tab=cell')->assertSee('어머니 수술을 위해');
        $this->actingAs($outsider)->get('/prayer?tab=cell')->assertDontSee('어머니 수술을 위해');
        $this->actingAs($outsider)->get('/prayer?tab=church')->assertDontSee('어머니 수술을 위해');
        $this->actingAs($pastor)->get('/prayer?tab=cell')->assertSee('어머니 수술을 위해');
    }

    /**
     * A request for the 목회자 is seen by the 목회자 and its writer alone.
     */
    public function test_a_pastor_request_is_read_by_the_pastor_alone(): void
    {
        $writer = $this->member();
        $other = $this->member();
        $pastor = $this->member(null, '부목사');
        $admin = $this->member();

        $this->actingAs($writer)
            ->post('/prayer', ['scope' => 'pastor', 'body' => '가정 문제로 상담을 원합니다'])
            ->assertRedirect('/prayer?tab=mine');

        $this->get('/prayer?tab=mine')->assertSee('가정 문제로 상담을 원합니다');

        $this->actingAs($pastor)->get('/prayer?tab=pastor')->assertSee('가정 문제로 상담을 원합니다');

        foreach (['cell', 'church', 'pastor', 'mine'] as $tab) {
            $this->actingAs($other)->get('/prayer?tab='.$tab)->assertDontSee('가정 문제로 상담을 원합니다');
            $this->actingAs($admin)->get('/prayer?tab='.$tab)->assertDontSee('가정 문제로 상담을 원합니다');
        }
    }

    /**
     * Only the writer marks a request answered; the writer and a 목회자
     * may remove it.
     */
    public function test_answering_and_removing(): void
    {
        $writer = $this->member();
        $other = $this->member();
        $pastor = $this->member(null, '전도사');

        $this->actingAs($writer)->post('/prayer', ['scope' => 'church', 'body' => '취업을 위해']);
        $request = PrayerRequest::query()->sole();

        $this->actingAs($other)->post('/prayer/'.$request->id.'/answered')->assertForbidden();
        $this->actingAs($other)->delete('/prayer/'.$request->id)->assertForbidden();

        $this->actingAs($writer)->post('/prayer/'.$request->id.'/answered')->assertRedirect();
        $this->assertTrue($request->fresh()->is_answered);
        $this->get('/prayer?tab=church')->assertSee('응답받음');

        $this->actingAs($pastor)->delete('/prayer/'.$request->id)->assertRedirect();
        $this->assertSame(0, PrayerRequest::count());
    }

    /**
     * Somebody not yet in a 셀 cannot share with one.
     */
    public function test_sharing_with_a_cell_needs_a_cell(): void
    {
        $this->actingAs($this->member())
            ->from('/prayer')
            ->post('/prayer', ['scope' => 'cell', 'body' => '기도'])
            ->assertRedirect('/prayer')
            ->assertSessionHasErrors('scope');

        $this->assertSame(0, PrayerRequest::count());
    }
}
