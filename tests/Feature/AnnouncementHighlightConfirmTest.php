<?php

namespace Tests\Feature;

use App\Filament\Resources\Announcements\Pages\CreateAnnouncement;
use App\Filament\Resources\Announcements\Pages\EditAnnouncement;
use App\Models\Announcement;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The admin panel must ask before the home page 하이라이트 changes
 * hands, on both the create and the edit page.
 */
class AnnouncementHighlightConfirmTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The announcement currently holding the highlight.
     */
    private Announcement $holder;

    /**
     * Seed roles and the Announcement permissions, then sign in.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        foreach (['ViewAny', 'View', 'Create', 'Update', 'Delete'] as $prefix) {
            Permission::findOrCreate("{$prefix}:Announcement", 'web');
        }

        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->actingAs($user);

        Announcement::query()->update(['is_highlighted' => false]);

        $this->holder = Announcement::factory()->create(['is_highlighted' => true]);
    }

    /**
     * Flagging another notice on the edit page raises the modal and
     * leaves the highlight where it was until it is confirmed.
     */
    public function test_editing_asks_before_moving_the_highlight(): void
    {
        $challenger = Announcement::factory()->create();

        $page = Livewire::test(EditAnnouncement::class, ['record' => $challenger->slug])
            ->fillForm(['is_highlighted' => true])
            ->call('save')
            ->assertActionMounted('confirmHighlightTakeover');

        $this->assertTrue($this->holder->fresh()->is_highlighted);
        $this->assertFalse($challenger->fresh()->is_highlighted);

        $page->callMountedAction();

        $this->assertFalse($this->holder->fresh()->is_highlighted);
        $this->assertTrue($challenger->fresh()->is_highlighted);
    }

    /**
     * Creating a highlighted notice asks the same question, and nothing
     * is written while the modal is still open.
     */
    public function test_creating_asks_before_moving_the_highlight(): void
    {
        $page = Livewire::test(CreateAnnouncement::class)
            ->fillForm([
                'title' => '새 하이라이트',
                'content' => '<p>본문</p>',
                'is_highlighted' => true,
            ])
            ->call('create')
            ->assertActionMounted('confirmHighlightTakeover');

        $this->assertSame(0, Announcement::query()->where('title', '새 하이라이트')->count());
        $this->assertTrue($this->holder->fresh()->is_highlighted);

        $page->callMountedAction();

        $this->assertTrue(Announcement::query()->where('title', '새 하이라이트')->sole()->is_highlighted);
        $this->assertFalse($this->holder->fresh()->is_highlighted);
    }

    /**
     * Nothing is asked when no other notice holds the flag, nor when
     * the flag is left alone.
     */
    public function test_no_confirmation_when_the_highlight_is_not_contested(): void
    {
        Livewire::test(EditAnnouncement::class, ['record' => $this->holder->slug])
            ->fillForm(['title' => '제목 수정'])
            ->call('save')
            ->assertActionNotMounted()
            ->assertHasNoFormErrors();

        $this->assertSame('제목 수정', $this->holder->fresh()->title);

        $this->holder->update(['is_highlighted' => false]);
        $other = Announcement::factory()->create();

        Livewire::test(EditAnnouncement::class, ['record' => $other->slug])
            ->fillForm(['is_highlighted' => true])
            ->call('save')
            ->assertActionNotMounted();

        $this->assertTrue($other->fresh()->is_highlighted);
    }
}
