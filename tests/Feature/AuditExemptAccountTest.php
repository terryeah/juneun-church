<?php

namespace Tests\Feature;

use App\Filament\Resources\Activities\Schemas\ActivityChanges;
use App\Filament\Resources\Announcements\Pages\ListAnnouncements;
use App\Filament\Support\Author;
use App\Models\Announcement;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Covers the account that maintains the site staying out of the log.
 *
 * The trail is there to show what the church's own people did, and the
 * account doing the building would otherwise be the loudest voice in
 * it. Its comings and goings are not recorded at all; what it changes
 * on the site still is, but without a name against it.
 */
class AuditExemptAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);
    }

    /**
     * An exempt account, and an ordinary one to prove the exemption is
     * what makes the difference rather than the test setup.
     *
     * @return array{0: User, 1: User}
     */
    private function accounts(): array
    {
        return [
            User::factory()->create(['is_audit_exempt' => true]),
            User::factory()->create(),
        ];
    }

    /**
     * Pages the exempt account opens leave nothing behind.
     */
    public function test_page_openings_are_not_recorded(): void
    {
        [$exempt, $ordinary] = $this->accounts();

        $this->actingAs($exempt)->get('/news')->assertOk();

        $this->assertCount(0, Activity::query()->where('log_name', 'page')->get());

        $this->actingAs($ordinary)->get('/news')->assertOk();

        $this->assertCount(1, Activity::query()->where('log_name', 'page')->get());
    }

    /**
     * So do its sign-ins and sign-outs.
     */
    public function test_signing_in_is_not_recorded(): void
    {
        [$exempt] = $this->accounts();

        $exempt->forceFill(['password' => bcrypt('secret-password')])->save();

        auth()->attempt(['email' => $exempt->email, 'password' => 'secret-password']);
        auth()->logout();

        $this->assertCount(0, Activity::query()->where('log_name', 'auth')->get());
    }

    /**
     * What it changes on the site is still recorded - that history is
     * the point of the log - but it carries no causer, which is what
     * makes the screen read 시스템 rather than a person's name.
     */
    public function test_content_changes_are_recorded_without_a_name(): void
    {
        [$exempt] = $this->accounts();

        $this->actingAs($exempt);

        $announcement = Announcement::factory()->create();

        $recorded = Activity::query()
            ->where('subject_type', Announcement::class)
            ->where('subject_id', $announcement->getKey())
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($recorded, '변경 이력 자체는 남아야 합니다.');
        $this->assertNull($recorded->causer_id);
        $this->assertNull($recorded->causer_type);
    }

    /**
     * An ordinary account is still named, so the exemption cannot have
     * quietly stripped the causer from everybody.
     */
    public function test_an_ordinary_account_is_still_named(): void
    {
        [, $ordinary] = $this->accounts();

        $this->actingAs($ordinary);

        $announcement = Announcement::factory()->create();

        $recorded = Activity::query()
            ->where('subject_type', Announcement::class)
            ->where('subject_id', $announcement->getKey())
            ->first();

        $this->assertSame($ordinary->getKey(), $recorded?->causer_id);
    }

    /**
     * The 작성자 column follows the same rule as the log: an ordinary
     * account is named, the exempt one and a record with nobody behind
     * it both read 시스템.
     */
    public function test_the_author_column_does_not_name_an_exempt_account(): void
    {
        $this->seed(RoleSeeder::class);

        $staff = User::factory()->create();
        $staff->assignRole('super_admin');
        Permission::query()->firstOrCreate(['name' => 'ViewAny:Announcement', 'guard_name' => 'web']);
        $staff->givePermissionTo('ViewAny:Announcement');

        [$exempt, $ordinary] = $this->accounts();

        $byExempt = Announcement::factory()->create(['created_by' => $exempt->getKey()]);
        $byOrdinary = Announcement::factory()->create(['created_by' => $ordinary->getKey()]);
        $byNobody = Announcement::factory()->create(['created_by' => null]);

        Livewire::actingAs($staff)
            ->test(ListAnnouncements::class)
            ->assertTableColumnStateSet('author.name', '시스템', $byExempt)
            ->assertTableColumnStateSet('author.name', $ordinary->name, $byOrdinary)
            ->assertTableColumnStateSet('author.name', '시스템', $byNobody);

        /** 시스템 is greyed; a named person is not. */
        $column = Author::column('author.name', '작성자');

        $this->assertSame('gray', $column->getColor('시스템'));
        $this->assertNull($column->getColor($ordinary->name));
    }

    /**
     * A 작성자 change in the log reads the same way. A deleted account
     * keeps 삭제됨: that is a different answer from 시스템 and the trail
     * has to be able to say it.
     */
    public function test_the_log_does_not_name_an_exempt_account_as_an_author(): void
    {
        [$exempt, $ordinary] = $this->accounts();

        $announcement = Announcement::factory()->create(['created_by' => $ordinary->getKey()]);
        $announcement->update(['created_by' => $exempt->getKey()]);

        $updated = Activity::query()
            ->where('subject_type', Announcement::class)
            ->where('subject_id', $announcement->getKey())
            ->where('event', 'updated')
            ->firstOrFail();

        $rows = collect(ActivityChanges::rows($updated))->keyBy('field');

        $this->assertSame($ordinary->name.' #'.$ordinary->getKey(), $rows['작성자']['before']);
        $this->assertSame('시스템', $rows['작성자']['after']);
    }
}
