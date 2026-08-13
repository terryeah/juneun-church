<?php

namespace App\Console\Commands;

use App\Models\MembershipRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Strips the personal details out of long-settled 가입 신청.
 *
 * A request carries a birth date, a phone number, an email address and
 * a password hash. Once it has been decided, the church has no further
 * use for any of that: an approval has already copied what it needed
 * onto the 교적, and a refusal never needed it at all. The rows were
 * kept whole and for ever, with no way to delete one from the panel -
 * so the church was holding the birth date and password hash of every
 * person it had ever turned down.
 *
 * The row itself stays, because who decided what, when and on what
 * grounds is worth keeping. Only the applicant's details go.
 *
 * Whether a row is already done is read from redacted_at rather than
 * from its name: 이름 is typed by the applicant, and a sentinel they can
 * write is a retention control they can switch off.
 */
class RedactOldMembershipRequests extends Command
{
    protected $signature = 'membership:redact {--days=90 : How long after a decision to keep the details}';

    protected $description = '결정된 지 오래된 가입 신청에서 개인정보를 지웁니다';

    /**
     * Redact every request settled longer ago than the cutoff.
     */
    public function handle(): int
    {
        $cutoff = now()->subDays((int) $this->option('days'));

        $redacted = 0;

        MembershipRequest::query()
            ->whereNotNull('reviewed_at')
            ->where('reviewed_at', '<', $cutoff)
            ->whereNull('redacted_at')
            ->each(function (MembershipRequest $request) use (&$redacted): void {
                $request->forceFill([
                    'name' => MembershipRequest::REDACTED,
                    'birth_date' => '1900-01-01',
                    'phone' => MembershipRequest::REDACTED,
                    'email' => MembershipRequest::REDACTED.'+'.$request->getKey().'@juneun.invalid',
                    'password' => Str::random(60),
                    'note' => null,
                    'redacted_at' => now(),
                ])->saveQuietly();

                $redacted++;
            });

        $this->info($redacted.'건의 가입 신청에서 개인정보를 지웠습니다.');

        return self::SUCCESS;
    }
}
