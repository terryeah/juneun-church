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
use Illuminate\Support\Str;

/**
 * A church document (교회 문서) offered as a PDF in the 자료실.
 *
 * These are the standing forms and policies - registration cards,
 * expense claims, the safeguarding policy - as opposed to the weekly
 * 주보, which keeps its own table.
 */
#[Fillable(['title', 'description', 'file_path', 'published_at', 'created_by'])]
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
     * The PDF is private on the bucket now, but every address it was
     * ever served from is still cached at the edge, so a replaced or
     * deleted file is still worth purging.
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
        ];
    }

    /**
     * Scope to the documents the current visitor may see.
     *
     * 자료실 is 성도 전용 as a whole page, so the question is about the
     * reader rather than about the record: the forms and policies here
     * are the church's own paperwork, all of them. Anybody off the 교적
     * therefore selects no document at all, which is what keeps the
     * file endpoint closed - it asks this scope whether the reader may
     * have the row before streaming it.
     *
     * @param  Builder<Document>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->unless(
            (bool) Auth::user()?->isChurchMember(),
            fn (Builder $q) => $q->whereRaw('1 = 0'),
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
     * Address of the 문서 on this site.
     *
     * Always the application's own, never the object's: the file is
     * private on the bucket now, and only a request that comes through
     * here can be asked who is making it.
     */
    public function fileUrl(): string
    {
        return route('document.file', $this);
    }

    /**
     * The name the PDF is served and saved under.
     *
     * A Korean title slugs to nothing, so the date stands in. The point
     * is an ASCII name: the header's fallback filename cannot carry
     * Hangul, and without one the browser names the file after the last
     * part of the address - the record's id.
     */
    public function downloadName(): string
    {
        return (Str::slug($this->title) ?: 'Document_'.$this->published_at->format('Y_m_d')).'.pdf';
    }
}
