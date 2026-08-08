<?php

namespace Tests\Feature;

use App\Filament\Resources\Members\Schemas\MemberForm;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The roster form hands out ordinary roles only.
 */
class MemberRoleAssignmentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * developer is never offered, not even to a developer.
     */
    public function test_the_developer_role_is_never_offered(): void
    {
        $this->seed(RoleSeeder::class);

        foreach ([['developer'], ['super_admin'], ['admin']] as $roles) {
            $actor = User::factory()->create();
            $actor->syncRoles($roles);
            $this->actingAs($actor);

            $this->assertNotContains(
                'developer',
                MemberForm::assignableRoles()->pluck('name')->all(),
                'developer was offered to a '.implode(', ', $roles).'.',
            );
        }
    }

    /**
     * super_admin stays with a super admin, so nobody lifts an account
     * above their own.
     */
    public function test_super_admin_is_offered_only_to_a_super_admin(): void
    {
        $this->seed(RoleSeeder::class);

        $admin = User::factory()->create();
        $admin->syncRoles(['admin']);
        $this->actingAs($admin);

        $this->assertNotContains('super_admin', MemberForm::assignableRoles()->pluck('name')->all());

        $superAdmin = User::factory()->create();
        $superAdmin->syncRoles(['super_admin']);
        $this->actingAs($superAdmin);

        $this->assertContains('super_admin', MemberForm::assignableRoles()->pluck('name')->all());
    }
}
