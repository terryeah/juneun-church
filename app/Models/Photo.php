<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * A single photograph within an album, stored on the media disk.
 */
#[Fillable([
    'album_id',
    'filename',
    'original_filename',
    'path',
    'thumbnail_path',
    'width',
    'height',
    'file_size',
    'caption',
    'sort_order',
    'uploaded_by',
])]
class Photo extends Model
{
    use HasFactory, LogsModelActivity;

    /**
     * The album this photo belongs to.
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    /**
     * The user who uploaded the photo.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Public URL of the full-size image.
     */
    public function url(): string
    {
        return Storage::disk(config('filesystems.media'))->url($this->path);
    }

    /**
     * Public URL of the thumbnail, falling back to the full-size image.
     */
    public function thumbnailUrl(): string
    {
        return Storage::disk(config('filesystems.media'))->url($this->thumbnail_path ?? $this->path);
    }
}
