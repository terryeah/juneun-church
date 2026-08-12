<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * States, for each role, exactly which panel resources it may open.
 *
 * Written as a complete set rather than a list of things to deny. The
 * test this replaces named seven resources a 재정부 must not reach, out
 * of twenty-one - so a new resource arrived outside the check by
 * default, which is how 문서 came to be readable, creatable and
 * deletable by the role that exists only to count offerings. Anything
 * added from now on has to be placed here or this fails.
 */
class RoleReachTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            foreach (['ViewAny', 'View', 'Create', 'Update', 'Delete'] as $ability) {
                Permission::findOrCreate($ability.':'.class_basename($resource::getModel()), 'web');
            }
        }

        $this->seed(RolePermissionSeeder::class);
        Filament::setCurrentPanel('admin');
    }

    /**
     * What each role is allowed to open, named in full.
     *
     * @return array<string, array{0: string, 1: list<string>}>
     */
    public static function roles(): array
    {
        $content = [
            'AlbumResource', 'AnnouncementResource', 'BulletinResource', 'DocumentResource',
            'EventResource', 'MinistryResource', 'PhotoResource', 'SermonResource',
            'ServiceTypeResource', 'VideoResource',
        ];

        $administration = [
            'CellResource', 'MemberResource', 'MembershipRequestResource', 'OfferingResource',
            'PersonalOfferingResource', 'PositionResource', 'SiteSettingResource',
            'StaffMemberResource', 'UserResource',
        ];

        return [
            '일반회원' => ['general_member', []],
            '재정부' => ['finance_officer', ['OfferingResource', 'PersonalOfferingResource']],
            '편집자' => ['content_editor', $content],
            '관리자' => ['admin', [...$content, ...$administration]],
            '개발자' => ['developer', [...$content, ...$administration, 'ActivityResource', 'RoleResource']],
        ];
    }

    /**
     * @param  list<string>  $allowed
     */
    #[DataProvider('roles')]
    public function test_a_role_reaches_exactly_what_it_should(string $role, array $allowed): void
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user);

        $reached = [];

        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            /** @var class-string<resource> $resource */
            if ($resource::canAccess()) {
                $reached[] = class_basename($resource);
            }
        }

        sort($reached);
        sort($allowed);

        $this->assertSame($allowed, $reached, $role.' reaches a different set of screens than it should.');
    }

    /**
     * A 재정부 may not delete church documents either.
     *
     * canAccess() only answers for the list screen. 문서 had no policy
     * at all, and Filament allows what no policy refuses, so every
     * ability was open - not just the one this list checks.
     */
    public function test_the_finance_role_cannot_write_to_documents(): void
    {
        $user = User::factory()->create();
        $user->assignRole('finance_officer');

        $this->actingAs($user);

        $this->assertFalse($user->can('viewAny', Document::class));
        $this->assertFalse($user->can('create', Document::class));
        $this->assertFalse($user->can('delete', Document::factory()->make()));
    }
}
