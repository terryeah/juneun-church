<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A 성도's own 묵상 on one day's 큐티, readable by its writer alone.
 *
 * Deliberately without LogsModelActivity: the activity log stores the
 * changed attributes, and that would put every 묵상 in front of the
 * developers who read the log.
 */
#[Fillable(['devotion_id', 'user_id', 'body'])]
class DevotionNote extends Model
{
    /**
     * The day this 묵상 is on.
     */
    public function devotion(): BelongsTo
    {
        return $this->belongsTo(Devotion::class);
    }

    /**
     * The account that wrote it.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
