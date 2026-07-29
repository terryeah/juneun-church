<?php

namespace App\Filament\Support;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

/**
 * Global file upload behaviour that stores images as WebP.
 *
 * Applied to every Filament FileUpload component and powered entirely
 * by Intervention Image: raster images are converted to WebP (scaled to
 * a sensible maximum edge, EXIF orientation applied), while WebP and
 * GIF images and non-image files such as bulletin PDFs pass through
 * untouched. With the Imagick extension and libheif installed, iPhone
 * HEIC photos convert as well; without them the original is stored.
 */
class SaveUploadsAsWebp
{
    /**
     * Quality used for the WebP encoder.
     */
    private const QUALITY = 82;

    /**
     * Maximum width or height of a stored image in pixels.
     */
    private const MAX_DIMENSION = 2560;

    /**
     * Register the behaviour for all FileUpload components, along with
     * a generous image preview height so uploaded photos fill the
     * taller drop area instead of floating in it.
     */
    public static function register(): void
    {
        FileUpload::configureUsing(function (FileUpload $component): void {
            $component
                ->imagePreviewHeight('220')
                ->saveUploadedFileUsing(
                    static fn (FileUpload $component, TemporaryUploadedFile $file): string => static::store($component, $file),
                );
        });
    }

    /**
     * Convert an image binary to WebP, or null when not applicable.
     *
     * WebP inputs are processed as well so oversized uploads are
     * scaled down and recompressed; only GIF returns null by design
     * to preserve animation frames.
     */
    public static function toWebp(string $binary): ?string
    {
        if (str_starts_with($binary, 'GIF8')) {
            return null;
        }

        try {
            $driver = extension_loaded('imagick') ? ImagickDriver::class : GdDriver::class;

            return (new ImageManager($driver))
                ->decodeBinary($binary)
                ->scaleDown(self::MAX_DIMENSION, self::MAX_DIMENSION)
                ->encode(new WebpEncoder(quality: self::QUALITY))
                ->toString();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Store the uploaded file, converting images to WebP when possible.
     */
    protected static function store(FileUpload $component, TemporaryUploadedFile $file): string
    {
        $binary = (string) file_get_contents($file->getRealPath());

        $directory = trim((string) $component->getDirectory(), '/');
        $prefix = $directory === '' ? '' : $directory.'/';
        $options = ['visibility' => $component->getVisibility()];
        $disk = Storage::disk($component->getDiskName());

        $converted = static::toWebp($binary);

        if ($converted !== null) {
            $path = $prefix.Str::uuid().'.webp';
            $disk->put($path, $converted, $options);

            return $path;
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $path = $prefix.Str::uuid().'.'.$extension;
        $disk->put($path, $binary, $options);

        return $path;
    }
}
