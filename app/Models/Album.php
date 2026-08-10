<?php

namespace App\Models;

use App\Filament\Support\SaveUploadsAsWebp;
use App\Models\Concerns\GeneratesReadableSlug;
use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\PurgesCdnCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * A photo album (갤러리) grouping photos from a church event.
 */
#[Fillable([
    'title',
    'slug',
    'description',
    'event_date',
    'cover_photo_path',
    'cover_thumbnail_path',
    'is_published',
    'is_members_only',
    'created_by',
])]
class Album extends Model
{
    use GeneratesReadableSlug, HasFactory, LogsModelActivity, PurgesCdnCache;

    /**
     * The cover image and its thumbnail are served from the CDN. The
     * album's photographs purge themselves as they cascade.
     *
     * @return list<string>
     */
    public function cdnMediaColumns(): array
    {
        return ['cover_photo_path', 'cover_thumbnail_path'];
    }

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
            'is_members_only' => 'boolean',
        ];
    }

    /**
     * Generate a slug from the title when none has been supplied.
     */
    protected static function booted(): void
    {
        static::deleting(function (Album $album): void {
            $album->photos()->get()->each->delete();

            if ($album->cover_photo_path) {
                Storage::disk(config('filesystems.media'))->delete($album->cover_photo_path);
            }
        });

        static::saving(function (Album $album) {
            if (blank($album->slug)) {
                $album->slug = static::readableSlug(
                    $album->title,
                    'album',
                    ($album->event_date ?? now())->format('Ymd'),
                );
            }
        });
    }

    /**
     * The photos in this album, in display order.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->orderBy('sort_order')->orderBy('id');
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
     * Scope to the albums the current visitor may see.
     *
     * A 성도 전용 album is dropped from the query rather than hidden in
     * the markup, so neither its title nor the URL of any of its
     * photographs ever reaches a guest's response.
     *
     * @param  Builder<Album>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->published()->unless(
            Auth::check(),
            fn (Builder $q) => $q->where('is_members_only', false),
        );
    }

    /**
     * Public URL of the album cover, falling back to the first photo.
     */
    public function coverUrl(): ?string
    {
        if ($this->cover_thumbnail_path) {
            return Storage::disk(config('filesystems.media'))->url($this->cover_thumbnail_path);
        }

        if ($this->cover_photo_path) {
            return Storage::disk(config('filesystems.media'))->url($this->cover_photo_path);
        }

        return $this->photos()->first()?->thumbnailUrl();
    }

    /**
     * Generate and store the 800px cover thumbnail if it is missing.
     */
    public function refreshCoverThumbnail(): void
    {
        if (! $this->cover_photo_path || $this->cover_thumbnail_path) {
            return;
        }

        $disk = Storage::disk(config('filesystems.media'));
        $thumbnail = SaveUploadsAsWebp::thumbnail((string) $disk->get($this->cover_photo_path));

        if ($thumbnail !== null) {
            $path = dirname($this->cover_photo_path).'/thumbs/'.basename($this->cover_photo_path);
            $disk->put($path, $thumbnail);
            $this->forceFill(['cover_thumbnail_path' => $path])->saveQuietly();
        }
    }

    /**
     * Use the slug for route model binding on the public site.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
