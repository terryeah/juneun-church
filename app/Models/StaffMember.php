<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * A serving member of the church (섬기는 사람들).
 */
#[Fillable([
    'name',
    'position_id',
    'department',
    'photo',
    'bio',
    'email',
    'phone',
    'sort_order',
    'is_published',
])]
class StaffMember extends Model
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
            'is_published' => 'boolean',
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
     * Scope to members visible on the public site.
     *
     * @param  Builder<StaffMember>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * Public URL of the member photo on the media disk, if one exists.
     */
    public function photoUrl(): ?string
    {
        return $this->photo
            ? Storage::disk(config('filesystems.media'))->url($this->photo)
            : null;
    }
}
