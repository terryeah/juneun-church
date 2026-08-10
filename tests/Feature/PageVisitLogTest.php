<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * Covers the audit trail of who opened which page.
 *
 * Added while the site is new so the church can see where signed-in
 * people went. A visitor who never identifies themselves is not
 * followed, and background traffic is not mistaken for a page.
 */
class PageVisitLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);
    }

    /**
     * The visits log under their own name so they can be told apart
     * from the record of what people changed.
     *
     * @return Collection<int, Activity>
     */
    private function visits()
    {
        return Activity::query()->where('log_name', 'page')->get();
    }

    /**
     * A signed-in person's page opening is recorded, with the path, the
     * account and the address it came from.
     */
    public function test_a_signed_in_visit_is_recorded(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/news')->assertOk();

        $visits = $this->visits();

        $this->assertCount(1, $visits);
        $this->assertSame('/news', $visits->first()->description);
        $this->assertSame('visited', $visits->first()->event);
        $this->assertSame($user->getKey(), $visits->first()->causer_id);
        $this->assertNotNull($visits->first()->properties['ip'] ?? null);
    }

    /**
     * A guest is not followed. Anyone can read the public site without
     * saying who they are, and that stays true.
     */
    public function test_a_guest_is_not_recorded(): void
    {
        $this->get('/news')->assertOk();

        $this->assertCount(0, $this->visits());
    }

    /**
     * Every opening counts, refreshes included - the church asked for
     * the whole trail, not a summary of it.
     */
    public function test_a_repeat_visit_is_recorded_again(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/news')->assertOk();
        $this->actingAs($user)->get('/news')->assertOk();

        $this->assertCount(2, $this->visits());
    }

    /**
     * Background traffic is not a page. Recording Livewire's polling
     * would bury the trail rather than complete it.
     */
    public function test_background_traffic_is_not_recorded(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->get('/news');

        $this->assertCount(0, $this->visits());
    }

    /**
     * Nor is a link the browser fetched on its own, which would credit
     * someone with a page they never opened.
     */
    public function test_a_prefetch_is_not_recorded(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withHeaders(['Purpose' => 'prefetch'])
            ->get('/news');

        $this->assertCount(0, $this->visits());
    }

    /**
     * A page that was not found is not a visit to it.
     */
    public function test_a_missing_page_is_not_recorded(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/news/there-is-no-such-notice')
            ->assertNotFound();

        $this->assertCount(0, $this->visits());
    }

    /**
     * The admin panel is followed too, and it is the half that matters:
     * 성도 records, 헌금 내역 and the log itself all live there.
     *
     * A Filament panel lists its own middleware instead of joining the
     * 'web' group, so this is the guard on the panel registering the
     * middleware separately. Appending to the web group alone recorded
     * the public site and silently skipped every screen worth auditing.
     */
    public function test_an_admin_panel_page_is_recorded(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->syncRoles(['super_admin']);

        $this->actingAs($user)->get('/admin')->assertOk();

        $this->assertSame('/admin', $this->visits()->first()?->description);
    }

    /**
     * The sign-in screen is not a visit: nobody has said who they are
     * yet, and the account that lands there next is recorded from its
     * first page onwards.
     */
    public function test_the_panel_sign_in_screen_is_not_recorded(): void
    {
        $this->get('/admin/login')->assertOk();

        $this->assertCount(0, $this->visits());
    }

    /**
     * The query string is kept, because on this site it carries which
     * week of 헌금 내역 or which tab of 자료실 was opened.
     */
    public function test_the_query_string_is_kept(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/downloads?type=documents')
            ->assertOk();

        $this->assertStringContainsString(
            'type=documents',
            $this->visits()->first()->properties['url'] ?? '',
        );
    }

    /**
     * Nothing else in the query string is kept.
     *
     * Filament holds a table's search box in the address bar, so
     * reloading 교적 after searching for a phone number is an ordinary
     * page opening carrying a 성도's number - which would then sit in
     * the log for 180 days and in every backup, outliving the 교적
     * record it came from.
     */
    public function test_a_search_term_is_not_kept(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/downloads?type=documents&search=0400+000+000')
            ->assertOk();

        $url = $this->visits()->first()->properties['url'] ?? '';

        $this->assertStringContainsString('type=documents', $url);
        $this->assertStringNotContainsString('0400', $url);
        $this->assertStringNotContainsString('search', $url);
    }
}
