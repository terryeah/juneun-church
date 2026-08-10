<?php

namespace Tests\Feature;

use App\Filament\Resources\Activities\Pages\ListActivities;
use App\Models\Announcement;
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
        activity('auth')->event('failed_login')->log('로그인 실패');

        $id = $departed->getKey();
        $departed->delete();

        Livewire::actingAs($developer)
            ->test(ListActivities::class)
            ->assertSee('삭제된 계정 #'.$id)
            ->assertSee('시스템');
    }
}
