<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A 기도제목 a 성도 shares, with whom they chose: their 셀, every 성도,
 * or the 목회자 alone.
 *
 * Without LogsModelActivity for the reason 묵상 is: the log would put
 * every request in front of the developers who read it.
 */
#[Fillable(['user_id', 'scope', 'body', 'is_answered'])]
class PrayerRequest extends Model
{
    /**
     * Who a request may be shared with, in the order the form offers them.
     *
     * @var array<string, string>
     */
    public const SCOPES = [
        'cell' => '우리 셀',
        'church' => '모든 성도',
        'pastor' => '목회자에게만',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_answered' => 'boolean'];
    }

    /**
     * The 성도 who shared it.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Requests shared with a 셀 by the 성도 in it.
     *
     * @param  Builder<PrayerRequest>  $query
     */
    public function scopeForCell(Builder $query, int $cellId): void
    {
        $query->where('scope', 'cell')
            ->whereHas('user.member', fn (Builder $members) => $members->where('cell_id', $cellId));
    }

    /**
     * Whether this reader may take it down: the writer, or a 목회자.
     */
    public function canBeRemovedBy(User $user): bool
    {
        return $this->user_id === $user->getKey() || $user->isPastor();
    }
}
