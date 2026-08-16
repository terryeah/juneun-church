<?php

namespace App\Models;

use App\Models\Concerns\BuildsMediaUrls;
use App\Models\Concerns\GeneratesReadableSlug;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * A church announcement (교회 소식).
 *
 * Announcements are authored in the admin panel with the Filament rich
 * editor, may be pinned above the chronological list, and can expire
 * automatically via the optional expires_at timestamp. 교회 소식 is
 * 성도 전용 as a whole page, so no notice carries an audience of its
 * own; the home page still lists the latest titles to everybody.
 */
#[Fillable([
    'title',
    'slug',
    'content',
    'featured_image',
    'is_published',
    'is_pinned',
    'is_highlighted',
    'published_at',
    'expires_at',
    'created_by',
])]
class Announcement extends Model
{
    use BuildsMediaUrls, GeneratesReadableSlug, HasFactory, LogsModelActivity;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_pinned' => 'boolean',
            'is_highlighted' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * Generate a slug from the title when none has been supplied, and
     * keep the home page 하이라이트 down to a single announcement.
     */
    protected static function booted(): void
    {
        static::saving(function (Announcement $announcement) {
            if (blank($announcement->slug)) {
                $announcement->slug = static::readableSlug(
                    $announcement->title,
                    'news',
                    ($announcement->published_at ?? now())->format('Ymd'),
                );
            }
        });

        /**
         * The flag is taken, not shared: whoever is saved with it on
         * takes it off everyone else. This lives on the model rather
         * than in the admin panel so a seeder, a migration or tinker
         * cannot leave two announcements holding the highlight.
         */
        static::saved(function (Announcement $announcement) {
            if (! $announcement->is_highlighted) {
                return;
            }

            static::query()
                ->whereKeyNot($announcement->getKey())
                ->where('is_highlighted', true)
                ->update(['is_highlighted' => false]);
        });
    }

    /**
     * The user who created the announcement.
     */
    /**
     * Public URL of the 대표 이미지 on the media disk.
     *
     * The views used to build this themselves with Storage::disk(),
     * which resolves the R2 driver and constructs an S3 client - 17 MB
     * of AWS SDK, on a box with room for six workers, to join two
     * strings. The home page pays it on every visit because the
     * highlight card carries an image.
     */
    public function imageUrl(): ?string
    {
        return $this->featured_image ? static::mediaUrl($this->featured_image) : null;
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to announcements visible on the public site.
     *
     * @param  Builder<Announcement>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    /**
     * Scope to announcements the current visitor is allowed to read.
     *
     * This is the scope every public page uses. Who is reading is no
     * longer asked here: 교회 소식 is closed at the door by 교적
     * membership, and the home page deliberately lists the latest
     * titles to everybody, so a notice is either published or it is
     * not.
     *
     * @param  Builder<Announcement>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->published();
    }

    /**
     * Plain-text excerpt of the content for list pages.
     *
     * Block-level closing tags and line breaks become spaces before tags
     * are stripped, so separate paragraphs never run into each other.
     */
    public function excerpt(int $limit = 140): string
    {
        return Str::of($this->content)
            ->replaceMatches('/<(br|\/p|\/h[1-6]|\/li|\/div)[^>]*>/i', ' ')
            ->stripTags()
            ->squish()
            ->limit($limit)
            ->value();
    }

    /**
     * Intrinsic pixel dimensions of the 대표 이미지, or null when there is
     * no image or it cannot be read.
     *
     * The column stores a bare path, so unlike a gallery photo there is
     * no width and height to read from the database. Measuring the file
     * lets the article template publish width and height attributes, and
     * with them the browser reserves the exact box a portrait poster
     * needs before a single byte of it has arrived - the difference
     * between a clean load and the whole article jumping down the page.
     * The measurement is cached against the stored path, which is a
     * fresh UUID on every upload, so a replaced image measures itself
     * once and every later request is a cache read.
     *
     * @return array{width: int, height: int}|null
     */
    public function featuredImageDimensions(): ?array
    {
        if (blank($this->featured_image)) {
            return null;
        }

        return Cache::rememberForever(
            'announcement-image-size:'.$this->featured_image,
            function (): ?array {
                $binary = Storage::disk(config('filesystems.media'))->get($this->featured_image);

                if ($binary === null) {
                    return null;
                }

                $size = @getimagesizefromstring($binary);

                return $size === false ? null : ['width' => $size[0], 'height' => $size[1]];
            },
        );
    }

    /**
     * Use the slug for route model binding on the public site.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
