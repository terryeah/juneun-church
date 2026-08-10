<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use App\Models\Concerns\PurgesCdnCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * A weekly bulletin (주보) uploaded as a PDF.
 */
#[Fillable(['title', 'file_path', 'published_at', 'is_members_only', 'created_by'])]
class Bulletin extends Model
{
    use HasFactory, LogsModelActivity, PurgesCdnCache;

    /**
     * Remove the stored file alongside the record.
     */
    protected static function booted(): void
    {
        static::deleted(function (Bulletin $bulletin): void {
            Storage::disk(config('filesystems.media'))->delete($bulletin->file_path);
        });
    }

    /**
     * The PDF is served from the CDN.
     *
     * @return list<string>
     */
    public function cdnMediaColumns(): array
    {
        return ['file_path'];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'date',
            'is_members_only' => 'boolean',
        ];
    }

    /**
     * Scope to the bulletins the current visitor may see.
     *
     * A restricted bulletin is dropped from the query rather than hidden
     * in the markup, so neither its title nor the URL of its PDF ever
     * reaches a guest's response.
     *
     * @param  Builder<Bulletin>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->unless(
            (bool) Auth::user()?->isChurchMember(),
            fn (Builder $q) => $q->where('is_members_only', false),
        );
    }

    /**
     * The user who uploaded the bulletin.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Public URL of the PDF on the media disk.
     */
    public function fileUrl(): string
    {
        return Storage::disk(config('filesystems.media'))->url($this->file_path);
    }
}
