<?php

namespace Tests\Feature;

use App\Filament\Auth\EditProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Covers the 2단계 인증 card on /admin/profile.
 *
 * The card used to read as though two-factor was off even when it was
 * on, because the destructive action wore the only strong colour on it.
 * The restyle lives entirely in the HEAD_END stylesheet, so these tests
 * check both halves: that the state is in the markup for an account
 * with and without an authenticator app, that the stylesheet which
 * makes that state the loudest thing on the card is actually served,
 * and that turning two-factor off still stops for a confirmation.
 */
class AdminTwoFactorSectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Sign in an account that can reach the panel.
     *
     * @param  bool  $withTwoFactor  whether to give it an app secret
     * @return User the signed-in account
     */
    private function signIn(bool $withTwoFactor): User
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('super_admin');

        if ($withTwoFactor) {
            $user->forceFill(['app_authentication_secret' => 'ABCDEFGHIJKLMNOP'])->save();
        }

        $this->actingAs($user);

        return $user->fresh();
    }

    /**
     * An account with an authenticator app says so, in the badge the
     * stylesheet turns into the card's status band, and still offers
     * both of the actions that act on it.
     */
    public function test_an_account_with_two_factor_on_shows_that_it_is_on(): void
    {
        $this->signIn(withTwoFactor: true);

        $this->get('/admin/profile')
            ->assertOk()
            ->assertSee('인증 앱')
            ->assertSee('사용 중')
            ->assertSee('fi-badge fi-size-md fi-color fi-color-success', escape: false)
            ->assertSee('복구 코드 재생성')
            ->assertSee('2단계 인증 끄기');
    }

    /**
     * An account without one says so in the same place, and offers only
     * the action that switches it on.
     */
    public function test_an_account_with_two_factor_off_shows_that_it_is_off(): void
    {
        $this->signIn(withTwoFactor: false);

        $this->get('/admin/profile')
            ->assertOk()
            ->assertSee('인증 앱')
            ->assertSee('사용 안 함')
            ->assertSee('2단계 인증 켜기')
            ->assertDontSee('2단계 인증 끄기');
    }

    /**
     * The state is only the loudest thing on the card if the rules that
     * make it so reach the browser. They ride the panel's HEAD_END
     * stylesheet, which no test would otherwise notice going missing.
     */
    public function test_the_card_is_restyled_by_the_panel_stylesheet(): void
    {
        $this->signIn(withTwoFactor: true);

        $this->get('/admin/profile')
            ->assertOk()
            /** The status band, and the muted resting state of 끄기. */
            ->assertSee('#content\.app .fi-badge{', escape: false)
            ->assertSee('#content\.app .fi-ac .fi-link.fi-color-danger{', escape: false)
            /** 설명 above the actions rather than below them. */
            ->assertSee('#content\.app .fi-sc-actions>.fi-sc{order:2', escape: false)
            ->assertSee('#content\.app .fi-ac{order:4', escape: false);
    }

    /**
     * The card sits between the profile fields and the buttons that
     * save them, so the page ends on 저장 rather than stranding it in
     * the middle. Filament's own order is the other way around, and the
     * CSS that restyles the card is anchored on the id its position in
     * the schema decides, so both are pinned here - along with the save
     * the card is now nested inside, which is the thing the move could
     * plausibly have broken.
     */
    public function test_the_card_comes_before_the_form_actions(): void
    {
        $this->signIn(withTwoFactor: true);

        $page = $this->get('/admin/profile')->assertOk();

        $markup = $page->getContent();

        $card = strpos($markup, 'id="content.app"');
        $actions = strpos($markup, 'id="content.form-actions"');

        $this->assertIsInt($card, 'the 2단계 인증 card lost its #content\.app anchor');
        $this->assertIsInt($actions);
        $this->assertLessThan($actions, $card);

        /** And the buttons still belong to the form they submit. */
        $this->assertLessThan($card, strpos($markup, '<form id="form"'));

        $page->assertSee('저장')
            ->assertDontSee('변경 사항 저장');

        Livewire::test(EditProfile::class)
            ->fillForm(['name' => '새 이름'])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertSame('새 이름', Filament::auth()->user()->fresh()->name);
    }

    /**
     * Muting the action must not have cost it its confirmation: the
     * button opens a modal that asks what is about to happen and will
     * not proceed without a live code from the authenticator app.
     */
    public function test_turning_two_factor_off_still_asks_first(): void
    {
        $user = $this->signIn(withTwoFactor: true);

        $action = TestAction::make('disableAppAuthentication')->schemaComponent('app', 'content');

        $page = Livewire::test(EditProfile::class)
            ->mountAction($action)
            ->assertActionMounted($action);

        /** The modal that opened carries the warning the card no longer shouts. */
        $modal = $page->instance()->getMountedAction();

        $this->assertSame('2단계 인증을 끌까요?', $modal->getModalHeading());
        $this->assertStringContainsString('비밀번호만으로 로그인할 수 있어요', $modal->getModalDescription());
        $this->assertSame(
            '2단계 인증 끄기',
            collect($modal->getVisibleModalFooterActions())->first()?->getLabel(),
        );

        /** And it will not proceed on a bare confirmation. */
        $page->callMountedAction()->assertHasErrors();

        $this->assertNotNull($user->fresh()->getAppAuthenticationSecret());
    }
}
