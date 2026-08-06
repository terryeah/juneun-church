<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Defer\DeferredCallbackCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The panel's password reset request page is public, and Filament
 * answers it with the same wording whether or not the address belongs
 * to anybody. These tests cover the part the wording cannot hide: how
 * long the two answers take.
 *
 * An address that exists pays for hashing a reset token and, before the
 * fix, for an SMTP connection as well; one that does not returns after
 * a single SELECT. Left alone that difference tells a stranger who
 * attends this church, which is the disclosure the sign-up form was
 * already hardened against.
 */
class PasswordResetOracleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the roles, register an address that exists, and put the
     * admin panel in scope so the reset page can be mounted.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        User::factory()->create(['email' => 'exists@example.test'])->assignRole('member');

        Filament::setCurrentPanel('admin');
    }

    /**
     * The reset mail leaves after the response, not during it.
     *
     * Filament's notification implements ShouldQueue, but the server
     * runs no queue worker, so the connection it lands on decides
     * everything: 'database' would leave the mail undelivered and
     * 'sync' would block the request on SMTP for as long as the mail
     * host takes - and only for an address that exists.
     */
    public function test_the_reset_mail_is_sent_after_the_response_rather_than_on_the_request_thread(): void
    {
        $sends = 0;

        Event::listen(MessageSending::class, function () use (&$sends): void {
            $sends++;
        });

        $this->request('exists@example.test');

        $this->assertSame(0, $sends, '메일이 요청 스레드에서 발송되었습니다');

        app(DeferredCallbackCollection::class)->invoke();

        $this->assertSame(1, $sends, '응답 후에도 메일이 발송되지 않았습니다');
    }

    /**
     * An address that exists and one that does not are answered in
     * indistinguishable time, even when the mail host is slow.
     */
    public function test_a_known_address_is_answered_no_slower_than_an_unknown_one(): void
    {
        /** A fake slow SMTP host: 800 ms of connect, handshake and send. */
        Event::listen(MessageSending::class, fn () => usleep(800_000));

        $exists = $this->medianDuration('exists@example.test');
        $missing = $this->medianDuration('nobody@example.test');

        $this->assertLessThan(1.2, $exists / $missing, sprintf(
            'answered a known address in %.1f ms against %.1f ms for an unknown one',
            $exists,
            $missing,
        ));
    }

    /**
     * The padding has to clear the work an existing address pays for.
     *
     * Laravel pads every reset request up to auth.timebox_duration, but
     * its stock 200 ms floor is below one bcrypt at the 12 rounds this
     * site uses - so the token hashing an existing address pays for
     * overruns the padding and shows through.
     */
    public function test_the_timebox_floor_clears_the_cost_of_hashing_a_reset_token(): void
    {
        /** The 12 rounds production runs at, not the 4 the suite uses. */
        $start = microtime(true);
        Hash::make('a-reset-token', ['rounds' => 12]);
        $hashing = (microtime(true) - $start) * 1_000_000;

        $this->assertGreaterThan($hashing * 2, config('auth.timebox_duration'), sprintf(
            'a %.0f us floor cannot hide %.0f us of token hashing',
            config('auth.timebox_duration'),
            $hashing,
        ));
    }

    /**
     * The median of three submissions of the same address, so a single
     * slow sample cannot decide the comparison.
     *
     * The page is mounted once and submitted three times; only the
     * submission is timed, and mounting a Filament page is expensive
     * enough that repeating it would cost the suite real memory.
     */
    private function medianDuration(string $email): float
    {
        $page = Livewire::test(RequestPasswordReset::class)->fillForm(['email' => $email]);

        $samples = [];

        for ($i = 0; $i < 3; $i++) {
            /**
             * Both the panel's own 2-per-minute limit and the 60-second
             * reset throttle are cache and table state; clearing them
             * keeps every sample a full, fresh request.
             */
            cache()->flush();
            DB::table('password_reset_tokens')->delete();

            $start = microtime(true);

            $page->call('request');

            $samples[] = (microtime(true) - $start) * 1000;
        }

        sort($samples);

        return $samples[1];
    }

    /**
     * Mount the reset page and submit the given address.
     */
    private function request(string $email): void
    {
        Livewire::test(RequestPasswordReset::class)
            ->fillForm(['email' => $email])
            ->call('request');
    }
}
