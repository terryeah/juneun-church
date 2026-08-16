<?php

namespace Tests\Feature;

use App\Filament\Resources\MembershipRequests\Pages\ListMembershipRequests;
use App\Models\MembershipRequest;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Covers who may throw a 가입 신청 away.
 *
 * 승인 and 거절 both leave the request on the list with a name and a
 * date against them. Deleting takes that account of what happened with
 * it, so it belongs to whoever maintains the site rather than to the
 * office - a role check, like the activity log, which cannot be handed
 * out from the roles screen by accident.
 */
class MembershipRequestDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    /**
     * An account that can work the list, with the roles it is given.
     *
     * @param  array<int, string>  $roles
     */
    private function staff(array $roles): User
    {
        $user = User::factory()->create();

        foreach ($roles as $role) {
            $user->assignRole($role);
        }

        foreach (['ViewAny:MembershipRequest', 'View:MembershipRequest', 'Update:MembershipRequest', 'Delete:MembershipRequest'] as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
            $user->givePermissionTo($permission);
        }

        return $user;
    }

    /**
     * A request to work with.
     */
    private function request(): MembershipRequest
    {
        return MembershipRequest::create([
            'name' => '김철수',
            'birth_date' => '1980-03-02',
            'phone' => '0411222333',
            'email' => 'kim@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ]);
    }

    /**
     * The developer can delete one, and the row goes.
     */
    public function test_a_developer_can_delete_a_request(): void
    {
        $request = $this->request();

        Livewire::actingAs($this->staff(['super_admin', 'developer']))
            ->test(ListMembershipRequests::class)
            ->callTableAction('delete', $request)
            ->assertHasNoTableActionErrors();

        $this->assertSame(0, MembershipRequest::query()->count());
    }

    /**
     * An administrator without the developer role is not offered it.
     *
     * The Shield permission is granted here deliberately: it proves the
     * gate is the role and not the permission, so nobody can hand this
     * out from the roles screen.
     */
    public function test_an_administrator_cannot(): void
    {
        $request = $this->request();
        $administrator = $this->staff(['super_admin']);

        $this->assertTrue($administrator->can('Delete:MembershipRequest'));
        $this->assertFalse($administrator->can('delete', $request));

        Livewire::actingAs($administrator)
            ->test(ListMembershipRequests::class)
            ->assertTableActionHidden('delete', $request);

        $this->assertSame(1, MembershipRequest::query()->count());
    }
}
