<?php

namespace App\Models;

use App\Filament\Support\SaveUploadsAsWebp;
use App\Models\Concerns\BuildsMediaUrls;
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
    'type',
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
    use BuildsMediaUrls, GeneratesReadableSlug, HasFactory, LogsModelActivity, PurgesCdnCache;

    /** An album of photographs, stored on the church's own media host. */
    public const TYPE_PHOTO = 'photo';

    /** An album of videos, held on the church's YouTube channel. */
    public const TYPE_VIDEO = 'video';

    /**
     * The kinds an album may be, labelled for the panel and the site.
     *
     * @var array<string, string>
     */
    public const TYPES = [
        self::TYPE_PHOTO => '사진',
        self::TYPE_VIDEO => '동영상',
    ];

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

            /**
             * Both, not just the original: the 800px thumbnail is what
             * the grid actually served, and it was outliving the album
             * it belonged to - still readable by anyone holding its
             * address, including for a 성도 전용 album.
             */
            $covers = array_filter([$album->cover_photo_path, $album->cover_thumbnail_path]);

            if ($covers !== []) {
                Storage::disk(config('filesystems.media'))->delete($covers);
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
        /**
         * chaperone() hands each photo back its album, so a view that
         * links to the album a photo belongs to does not fetch it again
         * one row at a time. The home band did exactly that: ten photos,
         * ten extra queries, on the busiest page of the site.
         */
        return $this->hasMany(Photo::class)->chaperone()->orderBy('sort_order')->orderBy('id');
    }

    /**
     * The videos in this album, in display order.
     */
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Whether this album holds videos rather than photographs.
     */
    public function holdsVideos(): bool
    {
        return $this->type === self::TYPE_VIDEO;
    }

    /**
     * Scope to one kind of album.
     *
     * @param  Builder<Album>  $query
     */
    public function scopeOfType(Builder $query, string $type): void
    {
        $query->where('type', $type);
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
            (bool) Auth::user()?->isChurchMember(),
            fn (Builder $q) => $q->where('is_members_only', false),
        );
    }

    /**
     * Public URL of the album cover, falling back to the first photo.
     */
    public function coverUrl(): ?string
    {
        /**
         * Asked first, not last. An album switched to 동영상 keeps
         * whatever cover it was uploaded with - the form hides that
         * field rather than clearing it - and would otherwise go on
         * showing a photograph from its previous life.
         */
        if ($this->holdsVideos()) {
            return $this->videos()->first()?->thumbnailUrl();
        }

        if ($this->cover_thumbnail_path) {
            return static::mediaUrl($this->cover_thumbnail_path);
        }

        if ($this->cover_photo_path) {
            return static::mediaUrl($this->cover_photo_path);
        }

        return $this->photos()->first()?->thumbnailUrl();
    }

    /**
     * How many items the album holds, whichever kind it holds.
     */
    public function itemCount(): int
    {
        return $this->holdsVideos()
            ? ($this->videos_count ?? $this->videos()->count())
            : ($this->photos_count ?? $this->photos()->count());
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
