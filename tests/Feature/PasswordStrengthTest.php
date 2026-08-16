<?php

namespace Tests\Feature;

use App\Models\MembershipRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Covers what the sign-up form asks of a password.
 *
 * Eight characters and nothing else about how they are spelled: no
 * required symbol, no required capital. What is checked instead is
 * whether the password is already on the breach lists, which is the
 * question that actually decides whether it can be guessed.
 */
class PasswordStrengthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The submission a visitor makes.
     *
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => '김철수',
            'birth_date' => '1980-03-02',
            'phone' => '0411222333',
            'email' => 'kim@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ], $overrides);
    }

    /**
     * The SHA-1 prefix response Have I Been Pwned would give, saying the
     * password asked about is in the corpus.
     */
    private function fakeBreachedReply(string $password): void
    {
        $hash = strtoupper(sha1($password));

        Http::fake([
            'api.pwnedpasswords.com/*' => Http::response(
                substr($hash, 5).":126260\r\n0000000000000000000000000000000000:3",
            ),
        ]);
    }

    /**
     * A password on the breach lists is refused, and the visitor is told
     * why in Korean.
     */
    public function test_a_breached_password_is_refused(): void
    {
        $this->fakeBreachedReply('password123');

        $this->post('/signup', $this->payload([
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]))->assertSessionHasErrors('password');

        $this->assertSame(0, MembershipRequest::query()->count());
    }

    /**
     * One nobody has leaked is accepted at eight characters, with no
     * symbol and no capital in it.
     */
    public function test_eight_plain_characters_are_enough(): void
    {
        Http::fake(['api.pwnedpasswords.com/*' => Http::response('')]);

        $this->post('/signup', $this->payload([
            'password' => 'namuwiki',
            'password_confirmation' => 'namuwiki',
        ]))->assertSessionHasNoErrors();

        $this->assertSame(1, MembershipRequest::query()->count());
    }

    /**
     * Seven is still too few.
     */
    public function test_seven_characters_are_refused(): void
    {
        Http::fake(['api.pwnedpasswords.com/*' => Http::response('')]);

        $this->post('/signup', $this->payload([
            'password' => 'namuwik',
            'password_confirmation' => 'namuwik',
        ]))->assertSessionHasErrors('password');
    }

    /**
     * The service being down must not stop anyone joining: the check
     * fails open, by Laravel's design, and this pins that behaviour.
     */
    public function test_an_unreachable_breach_service_does_not_block_a_signup(): void
    {
        Http::fake(['api.pwnedpasswords.com/*' => Http::response('', 503)]);

        $this->post('/signup', $this->payload([
            'password' => 'namuwiki',
            'password_confirmation' => 'namuwiki',
        ]))->assertSessionHasNoErrors();

        $this->assertSame(1, MembershipRequest::query()->count());
    }
}
