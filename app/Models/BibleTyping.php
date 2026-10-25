<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One verse a 성도 has typed out in full. The tally behind 성경 필사's
 * leaderboard; nothing else is recorded about the attempt.
 */
#[Fillable(['user_id', 'bible_verse_id'])]
class BibleTyping extends Model
{
    /**
     * The 성도 who typed it.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The verse they typed.
     */
    public function verse(): BelongsTo
    {
        return $this->belongsTo(BibleVerse::class, 'bible_verse_id');
    }
}
