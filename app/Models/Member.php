<?php

namespace App\Models;

use App\Models\Concerns\BuildsMediaUrls;
use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\PurgesCdnCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Support\LogOptions;

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
    'new_family_completed_at',
    'head_id',
    'cell_id',
    'relationship',
    'notes',
    'bio',
    'sort_order',
    'is_published',
    'user_id',
])]
class Member extends Model
{
    use BuildsMediaUrls, HasFactory, LogsModelActivity, PurgesCdnCache;

    /**
     * Position names that never count as serving on the public people
     * page, even when the member holds the position or a department.
     *
     * @var list<string>
     */
    public const NON_SERVING_POSITIONS = ['성도', '집사', '권사', '장로'];

    /**
     * The roster photograph is served from the CDN.
     *
     * @return list<string>
     */
    public function cdnMediaColumns(): array
    {
        return ['photo'];
    }

    /**
     * Clean up what the roster record leaves behind when it is deleted.
     *
     * The photograph goes because a public bucket would otherwise keep
     * serving it, and purging the edge copy would achieve nothing while
     * the origin still had it. The login goes because members.user_id
     * is nullOnDelete, so the account would outlive the person it
     * belonged to: still able to sign in, still holding their email,
     * and unreachable from 사이트 유저, which is read-only and lists
     * accounts through their member.
     */
    protected static function booted(): void
    {
        static::deleting(function (Member $member): void {
            $member->user?->delete();
        });

        static::deleted(function (Member $member): void {
            if ($member->photo) {
                Storage::disk(config('filesystems.media'))->delete($member->photo);
            }
        });
    }

    /**
     * Personal details stay out of the activity log; only roster
     * structure changes (status, position, household) are recorded.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'status', 'position_id', 'department', 'cell_id', 'head_id', 'relationship', 'registered_at', 'new_family_completed_at'])
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
            'new_family_completed_at' => 'date',
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
     * The linked site login account, if this member has one.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to members shown on the public serving-people page: anyone
     * holding a position or serving in a department, published, and not
     * holding a lay position such as 성도 or 집사.
     *
     * @param  Builder<Member>  $query
     */
    public function scopeServing($query): void
    {
        /**
         * Published only. The page this feeds is public and the
         * controller's own docblock said "published members", but
         * nothing checked it - so a 성도 given a serving 직분 while
         * their card was still being filled in appeared on 섬기는
         * 사람들 the moment the 직분 was set, photograph and all.
         */
        $query->where('is_published', true)
            ->where(fn ($inner) => $inner->whereNotNull('position_id')->orWhereNotNull('department'))
            ->withoutLayPositions();
    }

    /**
     * Scope excluding members whose position is a lay one (성도, 집사,
     * 권사, 장로); members without a position are kept.
     *
     * @param  Builder<Member>  $query
     */
    public function scopeWithoutLayPositions($query): void
    {
        $query->whereDoesntHave('position', fn ($positions) => $positions->whereIn('name', self::NON_SERVING_POSITIONS));
    }

    /**
     * Public URL of the member photo on the media disk, if one exists.
     */
    public function photoUrl(): ?string
    {
        return $this->photo
            ? static::mediaUrl($this->photo)
            : null;
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

    /**
     * The cell small group (셀) this member belongs to.
     */
    public function cell(): BelongsTo
    {
        return $this->belongsTo(Cell::class);
    }
}
