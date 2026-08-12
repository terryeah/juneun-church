<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Support\LogOptions;

/**
 * An individual's giving record (개인 헌금) for a given Sunday. The
 * giver is linked to the roster when they are on it; the name is kept
 * either way, because visitors and family members give as well.
 */
#[Fillable(['offering_id', 'member_id', 'name', 'category', 'amount', 'note'])]
class PersonalOffering extends Model
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
            'amount' => 'decimal:2',
        ];
    }

    /**
     * The Sunday record this giving belongs to.
     */
    public function offering(): BelongsTo
    {
        return $this->belongsTo(Offering::class);
    }

    /**
     * The roster member who gave, when they are on the roster.
     */
    /**
     * What changed, not how much.
     *
     * The default logs every fillable column, so editing a record
     * copied the giver's name, the amount and the note into the
     * activity log - a second home for the church's giving data,
     * outside the 재정부 permissions that are meant to hold it, and
     * outliving the record if it is deleted. 성도 does the same thing
     * for the same reason.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['offering_id', 'member_id', 'category'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
