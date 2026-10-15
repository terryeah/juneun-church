<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One day's 큐티: a passage and a guide, usually written in a batch of
 * six or seven from the Sunday sermon.
 *
 * A day can be prepared ahead of time; it reaches 성도 on its own date.
 */
#[Fillable([
    'devotion_date',
    'title',
    'scripture',
    'passage',
    'content',
    'sermon_id',
    'is_published',
    'created_by',
])]
class Devotion extends Model
{
    use HasFactory, LogsModelActivity;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'devotion_date' => 'date',
            'is_published' => 'boolean',
        ];
    }

    /**
     * The Sunday sermon the guide was written from.
     */
    public function sermon(): BelongsTo
    {
        return $this->belongsTo(Sermon::class);
    }

    /**
     * The user who created the record.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Every 성도's 묵상 on this day. Only ever read narrowed to one writer.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(DevotionNote::class);
    }

    /**
     * Scope to days that are published and have arrived.
     *
     * @param  Builder<Devotion>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where('is_published', true)->whereDate('devotion_date', '<=', today());
    }
}
