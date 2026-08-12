<?php

namespace App\Models;

use App\Models\Concerns\BuildsMediaUrls;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A worship recording (예배) hosted on YouTube.
 *
 * Only the YouTube video identifier is stored; embedding and thumbnails
 * are derived from it. The schema is designed so a future YouTube API
 * integration can populate records automatically.
 */
#[Fillable([
    'title',
    'youtube_video_id',
    'thumbnail_path',
    'preacher',
    'sermon_date',
    'service_type_id',
    'scripture_reference',
    'description',
    'is_published',
    'created_by',
])]
class Sermon extends Model
{
    use BuildsMediaUrls, HasFactory, LogsModelActivity;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sermon_date' => 'date',
            'is_published' => 'boolean',
        ];
    }

    /**
     * The service type this sermon belongs to.
     */
    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    /**
     * The user who created the record.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to sermons visible on the public site.
     *
     * @param  Builder<Sermon>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * YouTube watch URL for progressive-enhancement fallback links.
     */
    public function youtubeUrl(): string
    {
        return 'https://www.youtube.com/watch?v='.$this->youtube_video_id;
    }

    /**
     * YouTube poster-frame URL used before the iframe is loaded.
     */
    public function thumbnailUrl(): string
    {
        if ($this->thumbnail_path) {
            return static::mediaUrl($this->thumbnail_path);
        }

        return 'https://i.ytimg.com/vi/'.$this->youtube_video_id.'/hqdefault.jpg';
    }
}
