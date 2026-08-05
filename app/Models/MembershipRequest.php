<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
     * Why a roster record was offered as a candidate, strongest match
     * first. The order is the ranking.
     *
     * @var list<string>
     */
    public const MATCH_REASONS = [
        '이름 + 생년월일 일치',
        '이름 일치',
        '전화번호 일치',
    ];

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
     * Approve the request: create the login with the password the
     * applicant chose, link it to the chosen roster record (or to a
     * freshly registered one) and stamp the review.
     *
     * Field handling mirrors the 사이트 계정 section of the member form.
     */
    public function approve(?Member $member, User $reviewer): User
    {
        return DB::transaction(function () use ($member, $reviewer): User {
            $user = new User;
            $user->name = $this->name;
            $user->email = $this->email;
            $user->password = $this->password;
            $user->created_by = $reviewer->getKey();
            $user->save();
            $user->syncRoles(['member']);

            /** A new roster record stays unpublished until an administrator fills it in. */
            $member ??= Member::create([
                'name' => $this->name,
                'birth_date' => $this->birth_date,
                'phone' => $this->phone,
                'email' => $this->email,
                'position_id' => Position::query()->where('name', '성도')->value('id'),
                'is_published' => false,
            ]);

            $member->forceFill(['user_id' => $user->getKey()])->saveQuietly();

            $this->forceFill([
                'status' => '승인',
                'matched_member_id' => $member->getKey(),
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
            ])->save();

            return $user;
        });
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
            'password' => 'hashed',
        ];
    }
}
