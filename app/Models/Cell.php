<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A cell small group (셀) of congregation members, led by a 셀장 drawn
 * from the roster.
 */
#[Fillable(['name', 'leader_id', 'description', 'sort_order'])]
class Cell extends Model
{
    use HasFactory, LogsModelActivity;

    /**
     * Derive the cell name from its leader (셀장 이름 + ' 셀') on every
     * save, so it follows leader changes. When the leader has been
     * deleted (leader_id nulled by the database), the last derived
     * name is kept.
     */
    protected static function booted(): void
    {
        static::saving(function (Cell $cell): void {
            $leaderName = $cell->leader_id
                ? Member::query()->whereKey($cell->leader_id)->value('name')
                : null;

            if ($leaderName !== null) {
                $cell->name = $leaderName.' 셀';
            }
        });
    }

    /**
     * The member leading this cell (셀장).
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'leader_id');
    }

    /**
     * The members belonging to this cell (셀원).
     */
    public function members(): HasMany
    {
        return $this->hasMany(Member::class)->orderBy('name');
    }
}
