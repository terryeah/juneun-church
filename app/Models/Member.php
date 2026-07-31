<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A congregation member (성도) on the church roster.
 *
 * Households are modelled through head_id: the head of a household has
 * no head themselves, and family members point to the head with their
 * relationship (배우자, 자녀, ...) recorded.
 */
#[Fillable([
    'name',
    'gender',
    'birth_date',
    'phone',
    'email',
    'address',
    'photo',
    'position_id',
    'department',
    'baptism_type',
    'baptism_date',
    'status',
    'registered_at',
    'head_id',
    'relationship',
    'notes',
])]
class Member extends Model
{
    use HasFactory, LogsModelActivity;

    /**
     * Personal details stay out of the activity log; only roster
     * structure changes (status, position, household) are recorded.
     */
    public function getActivitylogOptions(): \Spatie\Activitylog\Support\LogOptions
    {
        return \Spatie\Activitylog\Support\LogOptions::defaults()
            ->logOnly(['name', 'status', 'position_id', 'department', 'head_id', 'relationship', 'registered_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
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
            'baptism_date' => 'date',
            'registered_at' => 'date',
        ];
    }

    /**
     * The position this member holds.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * The head of this member's household.
     */
    public function head(): BelongsTo
    {
        return $this->belongsTo(self::class, 'head_id');
    }

    /**
     * Family members belonging to this member's household.
     */
    public function family(): HasMany
    {
        return $this->hasMany(self::class, 'head_id');
    }
}
