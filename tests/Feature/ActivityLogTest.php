<?php

namespace Tests\Feature;

use App\Filament\Resources\Activities\Pages\ListActivities;
use App\Filament\Resources\Activities\Schemas\ActivityChanges;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\Member;
use App\Models\Position;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * Tests for the developer-only activity log.
 */
class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the application roles.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    /**
     * Developers can open the activity log.
     */
    public function test_developers_can_view_the_activity_log(): void
    {
        $developer = User::factory()->create();
        $developer->assignRole('developer');

        $this->actingAs($developer)
            ->get('/admin/activity-log')
            ->assertStatus(200);
    }

    /**
     * Administrators without the developer role are refused.
     */
    public function test_admins_cannot_view_the_activity_log(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get('/admin/activity-log')
            ->assertStatus(403);
    }

    /**
     * Content changes are recorded with their author.
     */
    public function test_model_changes_are_recorded(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $announcement = Announcement::factory()->create();
        $announcement->update(['title' => '수정된 제목']);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Announcement::class,
            'subject_id' => $announcement->id,
            'event' => 'created',
        ]);

        $updated = Activity::query()
            ->where('subject_type', Announcement::class)
            ->where('subject_id', $announcement->id)
            ->where('event', 'updated')
            ->first();

        $this->assertNotNull($updated);
        $this->assertSame('수정된 제목', $updated->attribute_changes['attributes']['title']);
    }

    /**
     * Sign-ins are recorded in the auth log.
     */
    public function test_logins_are_recorded(): void
    {
        $user = User::factory()->create(['password' => bcrypt('secret-password')]);

        auth()->attempt(['email' => $user->email, 'password' => 'secret-password']);

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'auth',
            'event' => 'login',
            'causer_id' => $user->id,
        ]);
    }

    /**
     * The sidebar must hide the activity log from non-developers.
     */
    public function test_activity_log_navigation_is_hidden_from_admins(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get('/admin/analytics')
            ->assertOk()
            ->assertDontSee('활동 기록');

        $developer = User::factory()->create();
        $developer->assignRole('developer');

        $this->actingAs($developer)
            ->get('/admin/analytics')
            ->assertOk()
            ->assertSee('활동 기록');
    }

    /**
     * A page opening says which page, on the table itself.
     *
     * The path is the only thing such a row carries, and it lives in
     * the description, which this table does not show. Without this the
     * 대상 column held a dash and the trail could only be read one row
     * at a time through the view modal.
     */
    public function test_a_page_visit_names_the_page_on_the_table(): void
    {
        $developer = User::factory()->create();
        $developer->assignRole('developer');

        activity('page')
            ->causedBy(User::factory()->create())
            ->event('visited')
            ->withProperties(['url' => 'https://www.juneun.com/giving', 'ip' => '1.2.3.4'])
            ->log('/giving');

        Livewire::actingAs($developer)
            ->test(ListActivities::class)
            ->assertSee('/giving');
    }

    /**
     * Closing someone's 사이트 계정 must not turn their history into
     * the site's own.
     *
     * The account is deleted outright and the log holds no link back to
     * it, so those rows would otherwise read '시스템' - which is what a
     * failed sign-in reads, and would say the site did what a person
     * did. The id survives in the column, so it stands in.
     */
    public function test_a_deleted_account_is_still_named(): void
    {
        $developer = User::factory()->create();
        $developer->assignRole('developer');

        $departed = User::factory()->create();
        activity('auth')->causedBy($departed)->event('login')->log('로그인');
        $id = $departed->getKey();
        $departed->delete();

        Livewire::actingAs($developer)
            ->test(ListActivities::class)
            ->assertSee('삭제된 계정 #'.$id)
            ->assertSee('시스템');
    }

    /**
     * A change is readable without decoding it.
     *
     * The view modal used to print the stored JSON, so a 직분 change
     * read as 'position_id: 4' next to 'position_id: 9' in two separate
     * objects. Each column now gets one row naming itself in Korean,
     * with foreign keys resolved and booleans spelled out.
     */
    public function test_a_change_is_shown_as_a_labelled_before_and_after(): void
    {
        $lay = Position::factory()->create(['name' => '성도'])->getKey();
        $pastoral = Position::factory()->create(['name' => '전도사'])->getKey();

        $member = Member::factory()->create(['position_id' => $lay, 'status' => '재적']);
        $member->update(['position_id' => $pastoral, 'status' => '장기결석']);

        $updated = Activity::query()
            ->where('subject_type', Member::class)
            ->where('subject_id', $member->getKey())
            ->where('event', 'updated')
            ->firstOrFail();

        $rows = collect(ActivityChanges::rows($updated))->keyBy('field');

        $this->assertSame('성도 #'.$lay, $rows['직분']['before']);
        $this->assertSame('전도사 #'.$pastoral, $rows['직분']['after']);
        $this->assertSame('재적', $rows['상태']['before']);
        $this->assertSame('장기결석', $rows['상태']['after']);
    }

    /**
     * A deletion still says what was deleted.
     *
     * logOnlyDirty() files the record's final state under 'old', not
     * 'attributes', so reading only 'attributes' left the modal showing
     * nothing at all for the one event where the record itself is gone
     * and the log is the only copy left.
     */
    public function test_a_deletion_shows_the_record_that_was_lost(): void
    {
        $announcement = Announcement::factory()->create(['title' => '삭제될 공지']);
        $announcement->delete();

        $deleted = Activity::query()
            ->where('subject_type', Announcement::class)
            ->where('event', 'deleted')
            ->firstOrFail();

        $rows = collect(ActivityChanges::rows($deleted))->keyBy('field');

        $this->assertSame('삭제될 공지', $rows['제목']['before']);
        $this->assertNull($rows['제목']['after']);
    }

    /**
     * A logged date is read back in the church's own timezone.
     *
     * Eloquent serialises a date cast into the log as UTC, so a Brisbane
     * 행사일 of 2024-06-24 is stored as 2024-06-23T14:00:00Z. Formatted
     * without converting it back, the log reported the day before.
     */
    public function test_a_logged_date_is_shown_in_the_church_timezone(): void
    {
        $event = Event::factory()->create(['event_date' => '2024-06-24']);

        $created = Activity::query()
            ->where('subject_type', Event::class)
            ->where('subject_id', $event->getKey())
            ->where('event', 'created')
            ->firstOrFail();

        $this->assertStringContainsString('2024-06-23T14:00:00', json_encode($created->attribute_changes));

        $rows = collect(ActivityChanges::rows($created))->keyBy('field');

        $this->assertSame('2024-06-24', $rows['행사일']['after']);
    }

    /**
     * A date column reads the same way whichever midnight its value was
     * serialised at.
     *
     * A value written before the site moved to Brisbane time sits at UTC
     * midnight rather than local, so deciding from the value whether to
     * show a time made one column render two ways - 2026-07-12 on one
     * row and 2026-05-11, 10:00 on the next. The cast decides now.
     */
    public function test_a_date_column_never_shows_a_time(): void
    {
        $event = Event::factory()->create(['event_date' => '2026-05-11', 'event_time' => '19:30:00']);

        $created = Activity::query()
            ->where('subject_type', Event::class)
            ->where('subject_id', $event->getKey())
            ->where('event', 'created')
            ->firstOrFail();

        /** Rewritten as a pre-timezone-shift row would have been stored. */
        $changes = $created->attribute_changes->toArray();
        $changes['attributes']['event_date'] = '2026-05-11T00:00:00.000000Z';
        $created->forceFill(['attribute_changes' => $changes])->save();

        $rows = collect(ActivityChanges::rows($created->fresh()))->keyBy('field');

        $this->assertSame('2026-05-11', $rows['행사일']['after']);

        /** A bare time column drops its seconds like every other time in the panel. */
        $this->assertSame('19:30', $rows['행사 시각']['after']);
    }

    /**
     * Shortening a time is for time columns holding real times.
     *
     * A 제목 of '19:30:00' is a title, and '99:99:99' in a time column
     * is evidence of something wrong - neither may be quietly rewritten
     * into something shorter that reads as a valid time.
     */
    public function test_only_a_real_time_in_a_time_column_is_shortened(): void
    {
        $announcement = Announcement::factory()->create();
        $announcement->update(['title' => '19:30:00']);

        $titled = Activity::query()
            ->where('subject_type', Announcement::class)
            ->where('subject_id', $announcement->getKey())
            ->where('event', 'updated')
            ->firstOrFail();

        $this->assertSame('19:30:00', collect(ActivityChanges::rows($titled))->firstWhere('field', '제목')['after']);

        $event = Event::factory()->create(['event_time' => '19:30:00']);

        $created = Activity::query()
            ->where('subject_type', Event::class)
            ->where('subject_id', $event->getKey())
            ->where('event', 'created')
            ->firstOrFail();

        $changes = $created->attribute_changes->toArray();
        $changes['attributes']['end_time'] = '99:99:99';
        $created->forceFill(['attribute_changes' => $changes])->save();

        $rows = collect(ActivityChanges::rows($created->fresh()))->keyBy('field');

        $this->assertSame('19:30', $rows['행사 시각']['after']);
        $this->assertSame('99:99:99', $rows['종료 시각']['after']);
    }

    /**
     * An impossible date is shown as it was stored rather than rolled
     * forward into a different one. Carbon turns 2026-02-30 into
     * 2026-03-02, and an audit log may not quietly rewrite its evidence.
     */
    public function test_an_impossible_date_is_left_as_it_was_stored(): void
    {
        $announcement = Announcement::factory()->create();
        $announcement->update(['title' => '2026-02-30']);

        $updated = Activity::query()
            ->where('subject_type', Announcement::class)
            ->where('subject_id', $announcement->getKey())
            ->where('event', 'updated')
            ->firstOrFail();

        $rows = collect(ActivityChanges::rows($updated))->keyBy('field');

        $this->assertSame('2026-02-30', $rows['제목']['after']);
    }

    /**
     * An auth or page row carries no attribute changes at all; what it
     * has is the request detail, which gets its own table.
     */
    public function test_request_details_are_listed_for_a_row_with_no_attribute_changes(): void
    {
        activity('page')
            ->event('visited')
            ->withProperties(['url' => 'https://www.juneun.com/giving', 'ip' => '1.2.3.4'])
            ->log('/giving');

        $visit = Activity::query()->where('log_name', 'page')->firstOrFail();

        $this->assertSame([], ActivityChanges::rows($visit));
        $this->assertSame(
            [
                ['field' => '페이지 주소', 'value' => 'https://www.juneun.com/giving'],
                ['field' => 'IP 주소', 'value' => '1.2.3.4'],
            ],
            ActivityChanges::context($visit),
        );
    }
}
