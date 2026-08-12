<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A video in an album, held on the church's YouTube channel.
 *
 * Nothing is uploaded here. The church posts to YouTube and the site
 * keeps the identifier, which is what the player and the thumbnail are
 * both built from.
 *
 * Most of these are unlisted on YouTube - viewable by anyone holding
 * the link and listed nowhere. That is why the identifier must not
 * reach a page a stranger can open: publishing it is the same as
 * publishing the video. Album::scopeVisible does that work, and the
 * albums these sit in are 성도 전용.
 */
#[Fillable([
    'album_id',
    'youtube_id',
    'title',
    'description',
    'sort_order',
    'created_by',
])]
class Video extends Model
{
    use HasFactory, LogsModelActivity;

    /**
     * The eleven-character identifier inside whatever was pasted in.
     *
     * The church copies links out of YouTube and out of BAND posts, and
     * they arrive in every shape: youtu.be, watch?v=, /embed/, /shorts/,
     * with a ?si= tracking tail, and - often enough to be worth
     * handling - with the ? or even the / missing after a copy-paste
     * through a chat app. All of those name the same video, so all of
     * them are accepted rather than rejected as malformed.
     *
     * Returns null when nothing that could be an identifier is there,
     * which is what the form's validation reports.
     */
    public static function extractYoutubeId(?string $input): ?string
    {
        $input = trim((string) $input);

        if ($input === '') {
            return null;
        }

        if (preg_match('#[?&]v=([A-Za-z0-9_-]{11})#', $input, $matches)) {
            return $matches[1];
        }

        if (preg_match('#(?:youtu\.be|/embed|/shorts|/live)/?([A-Za-z0-9_-]{11})#', $input, $matches)) {
            return $matches[1];
        }

        if (preg_match('#^[A-Za-z0-9_-]{11}$#', $input)) {
            return $input;
        }

        return null;
    }

    /**
     * The album this video belongs to.
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    /**
     * The administrator who added it.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The still YouTube publishes for the video.
     *
     * hqdefault exists for every video, including the ones with no
     * custom thumbnail, so it never leaves a card blank.
     */
    public function thumbnailUrl(): string
    {
        return 'https://i.ytimg.com/vi/'.$this->youtube_id.'/hqdefault.jpg';
    }

    /**
     * The player URL for the modal.
     *
     * youtube-nocookie.com is YouTube's own domain for embeds that do
     * not set advertising cookies until the viewer presses play, which
     * is the right default for a church showing family footage. The
     * frame is only written into the page once somebody opens a video,
     * so an album page loads no YouTube code at all.
     */
    public function embedUrl(): string
    {
        return 'https://www.youtube-nocookie.com/embed/'.$this->youtube_id.'?rel=0&autoplay=1';
    }

    /**
     * Where the video lives on YouTube, for anyone who would rather
     * watch it there.
     */
    public function watchUrl(): string
    {
        return 'https://www.youtube.com/watch?v='.$this->youtube_id;
    }
}
