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
 * A church document (교회 문서) offered as a PDF in the 자료실.
 *
 * These are the standing forms and policies - registration cards,
 * expense claims, the safeguarding policy - as opposed to the weekly
 * 주보, which keeps its own table.
 */
#[Fillable(['title', 'description', 'file_path', 'published_at', 'is_members_only', 'created_by'])]
class Document extends Model
{
    use HasFactory, LogsModelActivity, PurgesCdnCache;

    /**
     * Remove the stored file alongside the record.
     */
    protected static function booted(): void
    {
        static::deleted(function (Document $document): void {
            Storage::disk(config('filesystems.media'))->delete($document->file_path);
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
     * Scope to the documents the current visitor may see.
     *
     * A restricted document is dropped from the query rather than
     * hidden in the markup, so neither its title nor the URL of its PDF
     * ever reaches a guest's response.
     *
     * @param  Builder<Document>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->unless(
            (bool) Auth::user()?->isChurchMember(),
            fn (Builder $q) => $q->where('is_members_only', false),
        );
    }

    /**
     * The user who uploaded the document.
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
