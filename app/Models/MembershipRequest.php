<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use App\Notifications\MembershipApproved;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Activitylog\Support\LogOptions;

/**
 * A public sign-up request (가입 신청) awaiting administrator review.
 *
 * The submitted password is hashed the moment it is stored and is
 * carried over untouched when the request is approved, so the applicant
 * signs in with the password they chose and nobody ever handles it in
 * the clear. Only the public form fields are fillable; the review
 * columns are written through approve() and reject().
 */
#[Fillable(['name', 'birth_date', 'phone', 'email', 'password', 'note'])]
#[Hidden(['password'])]
class MembershipRequest extends Model
{
    use LogsModelActivity;

    /**
     * Request statuses, used as the list tabs.
     *
     * @var array<string, string>
     */
    public const STATUSES = [
        '대기' => '대기',
        '승인' => '승인',
        '거절' => '거절',
    ];

    /**
     * What a redacted field reads as once membership:redact has stripped
     * a long-settled request.
     *
     * Shared rather than private to the command because the 성도 form
     * shows the submission back and has to say the details are gone
     * instead of comparing against them. It is only ever what a redacted
     * field displays - whether a row has been redacted is redacted_at,
     * because 이름 is typed by the applicant and a sentinel they can
     * write is a retention control they can switch off.
     */
    public const REDACTED = '지움';

    /**
     * Why a roster record was offered as a candidate, strongest match
     * first. The order is the ranking, not a statement about identity:
     * name and birth date are whatever the applicant typed.
     *
     * @var list<string>
     */
    public const MATCH_REASONS = [
        '이름 + 생년월일 일치',
        '이름 일치',
        '전화번호 일치',
    ];

    /**
     * How a submitted field stands against the roster record it is
     * compared with.
     */
    public const VERDICT_SELF_DECLARED = '자기 신고';

    public const VERDICT_MATCH = '일치';

    public const VERDICT_CONFLICT = '불일치';

    /**
     * Fields the church may hold independently of the applicant, and so
     * the only ones whose agreement corroborates anything.
     *
     * @var list<string>
     */
    public const CORROBORATING_FIELDS = ['전화번호', '이메일'];

    /**
     * Ways an administrator may confirm that the applicant is the
     * person they claim to be, recorded on approval.
     *
     * @var array<string, string>
     */
    public const VERIFICATION_METHODS = [
        '전화 통화로 확인' => '전화 통화로 확인',
        '직접 만나 확인' => '직접 만나 확인',
        '가족 또는 셀장·교역자가 확인' => '가족 또는 셀장·교역자가 확인',
        '교회가 보관 중인 연락처와 일치' => '교회가 보관 중인 연락처와 일치',
        '기타' => '기타',
    ];

    /**
     * Record the review decision - who approved the request, which
     * roster record it was linked to and how the applicant's identity
     * was confirmed - so an approval can be audited afterwards.
     *
     * The columns are listed explicitly rather than relying on the
     * trait's logFillable() plus logExcept('password'): the submitted
     * password is a fillable attribute, and naming what is logged keeps
     * it out by construction. The free-text 확인 메모 stays on the
     * request only, as it may name other people.
     *
     * The applicant's name is not among them. Nothing rewrites the
     * activity log, and its rows are kept for 180 days from when they
     * were written against the 90 days from 처리일 that the request's
     * own details get - so logging the name here quietly outlived the
     * redaction that exists to destroy it. subject_id already says which
     * request a row is about, and for an approved one the name is on the
     * 교적 anyway.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'matched_member_id', 'reviewed_by', 'verification_method'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * Whether the applicant's details have been stripped from this row.
     */
    public function isRedacted(): bool
    {
        return $this->redacted_at !== null;
    }

    /**
     * The roster record the request was linked to on approval.
     */
    public function matchedMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'matched_member_id');
    }

    /**
     * The administrator who approved or rejected the request.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Existing roster records that plausibly belong to this applicant,
     * ranked strongest match first. Members that already own a login
     * are left out because a member links to at most one account.
     *
     * This is a hint for the reviewing administrator only - nothing is
     * ever linked without someone pressing 승인.
     *
     * @return Collection<int, array{member: Member, reason: string}>
     */
    public function candidates(): Collection
    {
        return Member::query()
            ->whereNull('user_id')
            ->where(fn ($query) => $query
                ->where('name', $this->name)
                ->orWhere(fn ($phone) => $phone->whereNotNull('phone')->where('phone', $this->phone)))
            ->get()
            ->map(fn (Member $member): array => [
                'member' => $member,
                'reason' => match (true) {
                    $member->name === $this->name && $member->birth_date?->isSameDay($this->birth_date) => self::MATCH_REASONS[0],
                    $member->name === $this->name => self::MATCH_REASONS[1],
                    default => self::MATCH_REASONS[2],
                },
            ])
            ->sortBy(fn (array $candidate): int => array_search($candidate['reason'], self::MATCH_REASONS, true))
            ->values();
    }

    /**
     * Compare what the applicant submitted against what the church
     * already holds on a roster record, field by field.
     *
     * A candidate proves nothing on its own: name and birth date are
     * both supplied by the applicant, and member names are published on
     * the public 섬기는 사람들 page, so agreement there only means the
     * applicant's own claims are internally consistent. Only a field
     * the church recorded independently - a phone number or an email
     * address already on the roster - can corroborate anything, and a
     * 불일치 is a reason to stop.
     *
     * @return list<array{field: string, submitted: ?string, held: ?string, verdict: string}>
     */
    public function comparison(Member $member): array
    {
        $fields = [
            '이름' => [$this->name, $member->name],
            '생년월일' => [$this->birth_date?->format('Y-m-d'), $member->birth_date?->format('Y-m-d')],
            '전화번호' => [$this->phone, $member->phone],
            '이메일' => [$this->email, $member->email],
        ];

        /** Only capitalisation is forgiven, so a formatting difference shows up as 불일치 rather than passing quietly. */
        $compare = fn (?string $value): string => Str::lower(trim((string) $value));

        return collect($fields)
            ->map(fn (array $values, string $field): array => [
                'field' => $field,
                'submitted' => filled($values[0]) ? $values[0] : null,
                'held' => filled($values[1]) ? $values[1] : null,
                'verdict' => match (true) {
                    blank($values[1]) => self::VERDICT_SELF_DECLARED,
                    $compare($values[0]) === $compare($values[1]) => self::VERDICT_MATCH,
                    default => self::VERDICT_CONFLICT,
                },
            ])
            ->values()
            ->all();
    }

    /**
     * One honest line about what a roster record corroborates, for the
     * candidate list and the approval modal.
     */
    public function corroboration(Member $member): string
    {
        $comparison = collect($this->comparison($member));

        $corroborated = $comparison
            ->where('verdict', self::VERDICT_MATCH)
            ->whereIn('field', self::CORROBORATING_FIELDS)
            ->pluck('field');

        return match (true) {
            $comparison->contains('verdict', self::VERDICT_CONFLICT) => '불일치 항목 있음',
            $corroborated->isNotEmpty() => '교회 기록 '.$corroborated->implode('·').' 일치',
            default => '신고 내용만 일치 (본인 확인 아님)',
        };
    }

    /**
     * Approve the request: create the login with the password the
     * applicant chose, put the applicant on the 교적 or deliberately
     * leave them off it, and stamp the review together with how the
     * administrator confirmed the applicant's identity.
     *
     * There are three outcomes, and the difference between them is the
     * 교적 record, because that is what 성도 전용 content answers to:
     * linking an existing 성도, registering a new one, or approving the
     * account alone for somebody who attends but the church has not
     * registered. The last one is why the request form can be answered
     * with something other than yes or no.
     *
     * The verification method is required on every path. It costs one
     * field, it is the same question in each case - is this person who
     * they say they are - and an account approved today may be put on
     * the 교적 tomorrow.
     *
     * Field handling mirrors the 사이트 계정 section of the member form.
     */
    public function approve(?Member $member, User $reviewer, string $verificationMethod, ?string $verificationNote = null, bool $registerOnRoster = true, bool $notify = true): User
    {
        $user = DB::transaction(function () use ($member, $reviewer, $verificationMethod, $verificationNote, $registerOnRoster): User {
            $user = new User;
            $user->name = $this->name;
            $user->email = $this->email;
            $user->password = $this->password;
            $user->created_by = $reviewer->getKey();
            $user->save();
            $user->syncRoles(['general_member']);

            /** A new roster record stays unpublished until an administrator fills it in. */
            if ($member === null && $registerOnRoster) {
                $member = Member::create([
                    'name' => $this->name,
                    'birth_date' => $this->birth_date,
                    'phone' => $this->phone,
                    'email' => $this->email,
                    'position_id' => Position::query()->where('name', '성도')->value('id'),
                    'is_published' => false,
                ]);
            }

            $member?->forceFill(['user_id' => $user->getKey()])->saveQuietly();

            $this->forceFill([
                'status' => '승인',
                'matched_member_id' => $member?->getKey(),
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
                'verification_method' => $verificationMethod,
                'verification_note' => $verificationNote,
            ])->save();

            return $user;
        });

        /**
         * Told after the transaction commits, never inside it: a mail
         * host having a bad afternoon may not undo an approval an
         * administrator has already made. The notification defers its
         * own send to after the response, so a slow SMTP handshake does
         * not hold the panel open either.
         */
        if ($notify) {
            $user->notify(new MembershipApproved($this->matched_member_id !== null));
        }

        return $user;
    }

    /**
     * Reject the request. No account and no roster record are created.
     */
    public function reject(User $reviewer): void
    {
        $this->forceFill([
            'status' => '거절',
            'reviewed_by' => $reviewer->getKey(),
            'reviewed_at' => now(),
        ])->save();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'reviewed_at' => 'datetime',
            'redacted_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
