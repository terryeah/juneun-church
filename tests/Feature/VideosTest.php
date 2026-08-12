<?php

namespace Tests\Feature;

use App\Filament\Resources\Videos\Pages\CreateVideo;
use App\Filament\Resources\Videos\VideoResource;
use App\Models\Album;
use App\Models\User;
use App\Models\Video;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Covers 동영상 in the panel: who reaches the screen, and what happens
 * to the address the church pastes into it.
 */
class VideosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            Permission::findOrCreate('ViewAny:'.class_basename($resource::getModel()), 'web');
        }

        $this->seed(RolePermissionSeeder::class);

        Filament::setCurrentPanel('admin');
    }

    /**
     * Somebody who may actually add a video.
     *
     * The panel's Create screen checks Create:Video, which the seeded
     * matrix only grants once that permission row exists - Shield
     * generates them, and the tests seed only the ViewAny half.
     */
    private function editor(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        foreach (['ViewAny:Video', 'View:Video', 'Create:Video', 'Update:Video', 'Delete:Video'] as $name) {
            Permission::findOrCreate($name, 'web');
            $user->givePermissionTo($name);
        }

        return $user;
    }

    /**
     * An editor manages video just as they manage photographs.
     */
    public function test_an_editor_reaches_the_screen(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole('content_editor');

        $this->actingAs($editor);

        $this->assertTrue(VideoResource::canAccess());
    }

    /**
     * A 일반회원 does not.
     */
    public function test_a_general_member_is_refused(): void
    {
        $member = User::factory()->create();
        $member->assignRole('general_member');

        $this->actingAs($member);

        $this->assertFalse(VideoResource::canAccess());
    }

    /**
     * Every shape of YouTube address the church has in hand resolves to
     * the same eleven characters.
     *
     * The malformed ones are not hypothetical: they came out of the
     * BAND post this album was built from, where a copy-paste through a
     * chat app had eaten the ? or the / before the identifier.
     *
     * @return array<string, array{0: string, 1: ?string}>
     */
    public static function addresses(): array
    {
        return [
            'youtu.be' => ['https://youtu.be/aaaaaaaaaaa?si=KNNTUmGryurQ50uX', 'aaaaaaaaaaa'],
            'watch' => ['https://www.youtube.com/watch?v=bbbbbbbbbbb', 'bbbbbbbbbbb'],
            'embed' => ['https://www.youtube.com/embed/ccccccccccc', 'ccccccccccc'],
            'shorts' => ['https://www.youtube.com/shorts/ddddddddddd', 'ddddddddddd'],
            'bare identifier' => ['eeeeeeeeeee', 'eeeeeeeeeee'],
            'missing question mark' => ['https://youtu.be/hhhhhhhhhhhsi=jwhcoEFeQ977Oeyy', 'hhhhhhhhhhh'],
            'missing slash as well' => ['https://youtu.beiiiiiiiiiiisi=oMv8_6odZ0Ay65LF', 'iiiiiiiiiii'],
            'leading underscore' => ['https://youtu.be/_nnnnnnnnnn?si=mWYNbAf6J', '_nnnnnnnnnn'],
            'not a video' => ['https://www.youtube.com/@juneun_church', null],
            'nothing at all' => ['', null],
        ];
    }

    #[DataProvider('addresses')]
    public function test_the_identifier_is_read_out_of_whatever_was_pasted(string $input, ?string $expected): void
    {
        $this->assertSame($expected, Video::extractYoutubeId($input));
    }

    /**
     * Pasting a whole address stores only the identifier.
     */
    public function test_the_form_stores_the_identifier_alone(): void
    {
        $album = Album::factory()->create(['type' => Album::TYPE_VIDEO]);

        Livewire::actingAs($this->editor())
            ->test(CreateVideo::class)
            ->fillForm([
                'album_id' => $album->getKey(),
                'title' => '청소년부 겨울 리트릿',
                'youtube_id' => 'https://youtu.be/aaaaaaaaaaa?si=KNNTUmGryurQ50uX',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        /** Scoped to this album: the church's own videos are seeded by a migration. */
        $this->assertSame('aaaaaaaaaaa', Video::query()->where('album_id', $album->getKey())->sole()->youtube_id);
    }

    /**
     * An address with no video in it is refused rather than stored.
     */
    public function test_an_unreadable_address_is_refused(): void
    {
        $album = Album::factory()->create(['type' => Album::TYPE_VIDEO]);

        Livewire::actingAs($this->editor())
            ->test(CreateVideo::class)
            ->fillForm([
                'album_id' => $album->getKey(),
                'title' => '엉뚱한 주소',
                'youtube_id' => 'https://www.youtube.com/@juneun_church',
            ])
            ->call('create')
            ->assertHasFormErrors(['youtube_id']);

        $this->assertSame(0, Video::query()->where('album_id', $album->getKey())->count());
    }

    /**
     * The player is built from the identifier on YouTube's no-cookie
     * domain, so an album page sets nothing until somebody presses play.
     */
    public function test_the_player_uses_the_no_cookie_domain(): void
    {
        $video = Video::factory()->create(['youtube_id' => 'aaaaaaaaaaa']);

        $this->assertStringStartsWith('https://www.youtube-nocookie.com/embed/aaaaaaaaaaa', $video->embedUrl());
        $this->assertSame('https://i.ytimg.com/vi/aaaaaaaaaaa/hqdefault.jpg', $video->thumbnailUrl());
    }
}
