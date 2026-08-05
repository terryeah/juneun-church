<?php

namespace App\Console\Commands;

use App\Filament\Support\SaveUploadsAsWebp;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Imports a single image from a URL into an existing album.
 *
 * The bulk Instagram importer creates one album per recent post, which
 * is too broad when only one graphic is wanted - a highlight poster,
 * say. This takes the image straight into an album that already exists,
 * converting it to webp and building the thumbnail through the same
 * pipeline the admin uploads use.
 */
class ImportPhotoFromUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'photos:import-url {album : Album slug} {url : Direct image URL} {--caption= : Optional caption}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import one image from a URL into an existing album';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $album = Album::query()->where('slug', $this->argument('album'))->first();

        if ($album === null) {
            $this->error("No album with slug {$this->argument('album')}.");

            return self::FAILURE;
        }

        $response = Http::withHeaders(['User-Agent' => 'Mozilla/5.0'])
            ->timeout(30)
            ->get($this->argument('url'));

        if (! $response->successful()) {
            $this->error('Download failed with status '.$response->status().'.');

            return self::FAILURE;
        }

        $binary = $response->body();
        $converted = SaveUploadsAsWebp::toWebp($binary);
        $extension = $converted !== null ? 'webp' : 'jpg';
        $binary = $converted ?? $binary;

        $disk = Storage::disk(config('filesystems.media'));
        $filename = $album->slug.'-'.($album->photos()->count() + 1).'.'.$extension;
        $path = 'albums/'.$album->slug.'/'.$filename;
        $disk->put($path, $binary);

        $thumbnail = SaveUploadsAsWebp::thumbnail($binary);
        $thumbnailPath = null;

        if ($thumbnail !== null) {
            $thumbnailPath = 'albums/'.$album->slug.'/thumbs/'.$filename;
            $disk->put($thumbnailPath, $thumbnail);
        }

        $photo = Photo::query()->create([
            'album_id' => $album->id,
            'filename' => $filename,
            'original_filename' => basename(parse_url($this->argument('url'), PHP_URL_PATH) ?: $filename),
            'path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'file_size' => strlen($binary),
            'caption' => $this->option('caption'),
            'sort_order' => ($album->photos()->count() + 1) * 10,
        ]);

        if (blank($album->cover_photo_path)) {
            $album->update(['cover_photo_path' => $photo->path]);
            $album->refreshCoverThumbnail();
        }

        $this->info("Imported photo {$photo->id} into {$album->title}.");

        return self::SUCCESS;
    }
}
