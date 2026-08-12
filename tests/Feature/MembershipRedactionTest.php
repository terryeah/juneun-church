<?php

namespace Tests\Feature;

use App\Models\MembershipRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers a settled 가입 신청 losing the applicant's details.
 *
 * The church has no use for a birth date, a phone number and a password
 * hash once it has decided - an approval copied what it needed onto the
 * 교적, and a refusal never needed any of it. Keeping them meant holding
 * the personal details of everyone the church had ever turned down,
 * with no way to delete a single one from the panel.
 */
class MembershipRedactionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A request settled long ago keeps its decision and loses the person.
     */
    public function test_a_settled_request_is_redacted(): void
    {
        $request = MembershipRequest::create([
            'name' => '김철수',
            'birth_date' => '1980-03-02',
            'phone' => '0400 111 222',
            'email' => 'kim@example.com',
            'password' => 'secret-password',
        ]);

        $request->forceFill(['status' => '거절', 'reviewed_at' => now()->subDays(120)])->save();

        $this->artisan('membership:redact')->assertSuccessful();

        $fresh = $request->fresh();

        $this->assertSame('지움', $fresh->name);
        $this->assertSame('지움', $fresh->phone);
        $this->assertStringNotContainsString('kim@example.com', $fresh->email);
        $this->assertNotSame('secret-password', $fresh->password);

        /** What the church decided, and when, is the part worth keeping. */
        $this->assertSame('거절', $fresh->status);
        $this->assertNotNull($fresh->reviewed_at);
    }

    /**
     * A request still waiting is left alone - it is the whole point of
     * the screen the office reviews it on.
     */
    public function test_a_waiting_request_is_untouched(): void
    {
        $request = MembershipRequest::create([
            'name' => '이영희',
            'birth_date' => '1990-05-05',
            'phone' => '0400 333 444',
            'email' => 'lee@example.com',
            'password' => 'secret-password',
        ]);

        $this->artisan('membership:redact')->assertSuccessful();

        $this->assertSame('이영희', $request->fresh()->name);
    }

    /**
     * So is one decided this week, so the office can still see who it
     * was while the decision is fresh.
     */
    public function test_a_recent_decision_is_untouched(): void
    {
        $request = MembershipRequest::create([
            'name' => '박민수',
            'birth_date' => '1985-01-01',
            'phone' => '0400 555 666',
            'email' => 'park@example.com',
            'password' => 'secret-password',
        ]);

        $request->forceFill(['status' => '승인', 'reviewed_at' => now()->subDays(3)])->save();

        $this->artisan('membership:redact')->assertSuccessful();

        $this->assertSame('박민수', $request->fresh()->name);
    }
}
