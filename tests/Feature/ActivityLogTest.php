<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
