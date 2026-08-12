<?php

namespace Tests\Feature;

use App\Http\Middleware\BrandThePanelTitle;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * Covers how the panel names itself: in the browser tab, and at the top
 * of the sidebar.
 */
class PanelBrandingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * An account that can open the panel.
     */
    private function staff(): User
    {
        $this->seed(RoleSeeder::class);
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->syncRoles(['super_admin']);

        return $user;
    }

    /**
     * The tab reads the church first, then the page, as the public site
     * does - not Filament's own '{page} - {church}'.
     */
    public function test_the_tab_names_the_church_first(): void
    {
        $church = (string) config('app.name');

        $this->actingAs($this->staff())
            ->get('/admin/members')
            ->assertOk()
            ->assertSee('<title>'.e($church.' · 성도').'</title>', escape: false);
    }

    /**
     * The sign-in screen is titled the same way, and it is the one page
     * a visitor sees before anything else.
     */
    public function test_the_sign_in_screen_is_titled_the_same_way(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('<title>'.e((string) config('app.name').' · 로그인').'</title>', escape: false);
    }

    /**
     * A page whose title is only the church's name is left alone rather
     * than doubled up.
     */
    /**
     * A title that is already just the church's name is left alone.
     *
     * Exercised against the middleware rather than a page, because no
     * screen in the panel currently has an empty title - which is why
     * the earlier version of this test proved nothing: it opened 성도,
     * whose title is '성도', so the branch under test was never
     * entered and the assertion held with or without the guard.
     */
    public function test_the_church_name_is_not_repeated(): void
    {
        $church = (string) config('app.name');

        $this->assertSame(
            '<title>'.$church.'</title>',
            $this->rewrite('<title>'.$church.'</title>'),
        );
    }

    /**
     * And the ordinary case still turns round.
     */
    public function test_the_two_halves_are_swapped(): void
    {
        $church = (string) config('app.name');

        $this->assertSame(
            '<title>'.$church.' · 성도 수정</title>',
            $this->rewrite('<title>성도 수정 - '.$church.'</title>'),
        );
    }

    /**
     * A title in some shape the middleware does not recognise is passed
     * through untouched rather than mangled.
     */
    public function test_an_unfamiliar_title_is_left_as_written(): void
    {
        $this->assertSame('<title>무언가 다른 것</title>', $this->rewrite('<title>무언가 다른 것</title>'));
    }

    /**
     * Runs one HTML document through the panel's title middleware.
     */
    private function rewrite(string $title): string
    {
        $response = app(BrandThePanelTitle::class)->handle(
            Request::create('/admin'),
            fn (): Response => new Response('<html><head>'.$title.'</head><body></body></html>', 200, [
                'Content-Type' => 'text/html; charset=UTF-8',
            ]),
        );

        preg_match('#<title>.*?</title>#s', (string) $response->getContent(), $matches);

        return $matches[0] ?? '';
    }

    /**
     * The church mark sits beside the name at the top of the sidebar.
     *
     * The mark inherits its colour rather than carrying one, which is
     * what makes it white on the dark theme and near-black on the light
     * one, so that is what is checked rather than a fixed fill.
     */
    public function test_the_sidebar_carries_the_mark_beside_the_name(): void
    {
        $response = $this->actingAs($this->staff())->get('/admin');

        $response->assertOk()->assertSee((string) config('app.name'));

        $logo = str((string) $response->getContent())->after('fi-logo')->before('</div>');

        $this->assertStringContainsString('<svg', (string) $logo, '사이드바에 로고가 없습니다.');
        $this->assertStringContainsString('currentColor', (string) $logo);
    }
}
