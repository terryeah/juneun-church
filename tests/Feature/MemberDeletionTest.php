<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Deleting a roster record must take its login with it.
 *
 * members.user_id is nullOnDelete, so without the model hook the
 * account survives the person: able to sign in, holding their email
 * against a future 가입 신청, and invisible to 사이트 유저, which lists
 * accounts through their member record and offers no delete action.
 */
class MemberDeletionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The linked account is deleted alongside the member.
     */
    public function test_deleting_a_member_deletes_the_linked_login(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->create(['user_id' => $user->id]);

        $member->delete();

        $this->assertNull(User::query()->find($user->getKey()));
        $this->assertNull(Member::query()->find($member->getKey()));
    }

    /**
     * A member with no login deletes without touching anyone else.
     */
    public function test_deleting_a_member_without_a_login_leaves_other_accounts_alone(): void
    {
        $other = User::factory()->create();

        Member::factory()->create()->delete();

        $this->assertNotNull(User::query()->find($other->getKey()));
    }
}
