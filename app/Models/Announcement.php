<?php

namespace App\Models;

use App\Models\Concerns\GeneratesReadableSlug;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * A church announcement (교회 소식).
 *
 * Announcements are authored in the admin panel with the Filament rich
 * editor, may be pinned above the chronological list, and can expire
 * automatically via the optional expires_at timestamp.
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
    use GeneratesReadableSlug, HasFactory, LogsModelActivity;

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
     * Use the slug for route model binding on the public site.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
