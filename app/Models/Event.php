<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A church event (교회 행사) shown in monthly grouped tables.
 */
#[Fillable([
    'title',
    'event_date',
    'event_time',
    'end_date',
    'location',
    'description',
    'is_published',
    'created_by',
])]
class Event extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'end_date' => 'date',
            'is_published' => 'boolean',
        ];
    }

    /**
     * The user who created the event.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to events visible on the public site.
     *
     * @param  Builder<Event>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }
}
