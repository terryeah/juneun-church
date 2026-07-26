<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * A photo album (갤러리) grouping photos from a church event.
 */
#[Fillable([
    'title',
    'slug',
    'description',
    'event_date',
    'cover_photo_path',
    'is_published',
    'created_by',
])]
class Album extends Model
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
            'is_published' => 'boolean',
        ];
    }

    /**
     * Generate a slug from the title when none has been supplied.
     */
    protected static function booted(): void
    {
        static::saving(function (Album $album) {
            if (blank($album->slug)) {
                $album->slug = Str::slug($album->title).'-'.Str::lower(Str::random(6));
            }
        });
    }

    /**
     * The photos in this album, in display order.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->orderBy('sort_order');
    }

    /**
     * The user who created the album.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to albums visible on the public site.
     *
     * @param  Builder<Album>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * Public URL of the album cover, falling back to the first photo.
     */
    public function coverUrl(): ?string
    {
        if ($this->cover_photo_path) {
            return Storage::disk(config('filesystems.media'))->url($this->cover_photo_path);
        }

        return $this->photos()->first()?->thumbnailUrl();
    }

    /**
     * Use the slug for route model binding on the public site.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
