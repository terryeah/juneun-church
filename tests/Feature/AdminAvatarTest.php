<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use App\Support\Initials;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Covers the admin panel avatar: the roster photo when the account has
 * one, initials drawn as text in the page when it does not, and no
 * request to ui-avatars.com in either case.
 */
class AdminAvatarTest extends TestCase
{
    use RefreshDatabase;

    /**
     * An account whose 성도 carries a photo shows that photo.
     */
    public function test_a_member_photo_is_used_as_the_avatar(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->create(['user_id' => $user->id, 'photo' => 'members/kim.webp']);

        $this->assertSame($member->photoUrl(), $user->fresh()->getFilamentAvatarUrl());
    }

    /**
     * No 성도 at all, or one with no photo on file, leaves the avatar
     * unresolved so the panel falls back to the initials.
     */
    public function test_an_account_without_a_photo_has_no_avatar_url(): void
    {
        $unlinked = User::factory()->create();

        $this->assertNull($unlinked->getFilamentAvatarUrl());

        $linked = User::factory()->create();
        Member::factory()->create(['user_id' => $linked->id, 'photo' => null]);

        $this->assertNull($linked->fresh()->getFilamentAvatarUrl());
    }

    /**
     * A Korean name drops the 성 and keeps the given name; a Latin name
     * keeps one letter per segment.
     *
     * @param  string  $name  the account name
     * @param  string  $expected  the characters expected in the circle
     */
    #[DataProvider('names')]
    public function test_the_initials_follow_the_name(string $name, string $expected): void
    {
        $this->assertSame($expected, Initials::for($name));
    }

    /**
     * Names and the initials they must produce.
     *
     * @return array<string, array{string, string}>
     */
    public static function names(): array
    {
        return [
            'three syllable Korean name' => ['양민규', '민규'],
            'two syllable Korean name' => ['한별', '한별'],
            'compound surname' => ['남궁민수', '민수'],
            'Latin name' => ['Terry Yang', 'TY'],
            'Korean name written with a space' => ['양 민규', '양민'],
        ];
    }

    /**
     * The dashboard draws the initials as text, in both places the
     * avatar appears - the topbar menu and the account widget - and
     * mentions ui-avatars.com nowhere.
     */
    public function test_the_dashboard_draws_the_initials_as_text(): void
    {
        $html = $this->actingAs($this->administrator('양민규'))
            ->get(Filament::getPanel('admin')->getUrl())
            ->assertOk()
            ->assertDontSee('ui-avatars.com')
            ->getContent();

        $this->assertMatchesRegularExpression('/fi-avatar-initials[^>]*>\s*<span[^>]*>민규<\/span>/u', $html);
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'fi-avatar-initials'));
    }

    /**
     * A name carrying markup characters is escaped, not injected.
     */
    public function test_a_name_with_markup_characters_is_escaped(): void
    {
        $html = $this->actingAs($this->administrator('<b> & Co'))
            ->get(Filament::getPanel('admin')->getUrl())
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('/fi-avatar-initials[^>]*>\s*<span[^>]*>&lt;&amp;C<\/span>/u', $html);
    }

    /**
     * An account with a photo gets the image, not the initials.
     */
    public function test_an_account_with_a_photo_renders_an_image(): void
    {
        $admin = $this->administrator('양민규');
        Member::factory()->create(['user_id' => $admin->id, 'photo' => 'members/yang.webp']);

        $html = $this->actingAs($admin->fresh())
            ->get(Filament::getPanel('admin')->getUrl())
            ->assertOk()
            ->assertSee('members/yang.webp', escape: false)
            ->getContent();

        $this->assertDoesNotMatchRegularExpression('/<span[^>]*fi-avatar-initials/u', $html);
    }

    /**
     * The login page reaches out to nobody either.
     */
    public function test_the_login_page_does_not_mention_ui_avatars(): void
    {
        $this->get(Filament::getPanel('admin')->getLoginUrl())
            ->assertOk()
            ->assertDontSee('ui-avatars.com');
    }

    /**
     * A super admin able to open the dashboard, named as given.
     */
    private function administrator(string $name): User
    {
        $this->seed(RoleSeeder::class);

        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            Permission::findOrCreate('ViewAny:'.class_basename($resource::getModel()), 'web');
        }

        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create(['name' => $name]);
        $user->assignRole('super_admin');

        return $user;
    }
}
