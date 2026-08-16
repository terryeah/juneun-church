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
#[Fillable(['title', 'file_path', 'published_at', 'created_by'])]
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
     * Scope to the bulletins the current visitor may see.
     *
     * 자료실 is 성도 전용 as a whole page, so the question is about the
     * reader rather than about the record: a 주보 carries the cell
     * lists, the rota and the offering record, and every one of them
     * does. Anybody off the 교적 therefore selects no bulletin at all,
     * which is what keeps the file endpoints closed - they ask this
     * scope whether the reader may have the row before streaming it.
     *
     * @param  Builder<Bulletin>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->unless(
            (bool) Auth::user()?->isChurchMember(),
            fn (Builder $q) => $q->whereRaw('1 = 0'),
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
     * Address of the 주보 on this site.
     *
     * Always the application's own, never the object's: the file is
     * private on the bucket now, and only a request that comes through
     * here can be asked who is making it.
     */
    public function fileUrl(): string
    {
        return $this->pdfUrl();
    }

    /**
     * The name the PDF is served and saved under.
     *
     * ASCII on purpose. Laravel's fallback filename runs the name
     * through Str::ascii(), which drops Hangul entirely, so a 주보
     * called 주일 예배 주보 arrived named nothing at all and the browser
     * fell back to the last part of the address - the record's id.
     */
    public function downloadName(): string
    {
        return 'Bulletin_'.$this->published_at->format('Y_m_d').'.pdf';
    }

    /**
     * Address of the PDF itself, as opposed to the page that shows it.
     */
    public function pdfUrl(): string
    {
        return route('bulletin.pdf', [$this, $this->downloadName()]);
    }
}
