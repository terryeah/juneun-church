<?php

namespace App\Filament\Support;

use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
 * by Intervention Image: raster images are converted to WebP, scaled to
 * a sensible maximum edge with EXIF orientation applied.
 *
 * A photograph that will not convert is refused rather than stored as
 * it arrived. A camera RAW or an unreadable HEIC kept in its original
 * form would sit on the CDN as a file most browsers cannot draw, and
 * the gallery would show a hole where a picture should be; better to
 * say so and let the upload be tried again.
 *
 * Two things are stored untouched, deliberately. A GIF keeps its frames
 * because every browser draws one anyway, and a file that is not an
 * image at all - the weekly 주보 PDF, a church form - is the document
 * itself rather than a photograph, so there is nothing to convert.
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
     * Produce a small WebP thumbnail for grid and slider views.
     *
     * Returns null for GIFs and non-image binaries, mirroring toWebp.
     */
    public static function thumbnail(string $binary, int $maxDimension = 800): ?string
    {
        if (str_starts_with($binary, 'GIF8')) {
            return null;
        }

        try {
            $driver = extension_loaded('imagick') ? ImagickDriver::class : GdDriver::class;

            return (new ImageManager($driver))
                ->decodeBinary($binary)
                ->scaleDown($maxDimension, $maxDimension)
                ->encode(new WebpEncoder(quality: 75))
                ->toString();
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Store the uploaded file, converting images to WebP when possible.
     *
     * The original never stays on the server. Livewire parks it in
     * storage/app/private/livewire-tmp while the form is being filled
     * in, and would sweep it up within a day on its own; it is deleted
     * here instead, as soon as the copy on the media disk exists, so an
     * untouched original is not sitting in a directory nobody looks at.
     */
    protected static function store(FileUpload $component, TemporaryUploadedFile $file): string
    {
        $binary = (string) file_get_contents($file->getRealPath());

        $directory = trim((string) $component->getDirectory(), '/');
        $prefix = $directory === '' ? '' : $directory.'/';
        $options = ['visibility' => $component->getVisibility()];
        $disk = Storage::disk($component->getDiskName());

        /** A 주보 PDF or a GIF is stored as it arrived; see the class note. */
        if (! static::mustConvert($binary)) {
            $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $path = $prefix.Str::uuid().'.'.$extension;
            $disk->put($path, $binary, $options);

            static::discard($file);

            return $path;
        }

        $converted = static::toWebp($binary);

        if ($converted === null) {
            static::discard($file);
            static::refuse($component);
        }

        $path = $prefix.Str::uuid().'.webp';
        $disk->put($path, $converted, $options);

        static::discard($file);

        return $path;
    }

    /**
     * Whether this upload has to become WebP before it is stored.
     *
     * Only two things are named as safe to keep as they arrived, and
     * everything else must convert. Asking the question the other way
     * round - is this an image? - was tried first and fails open: a
     * camera RAW that the server has no delegate for is also a file
     * finfo cannot name, so it would have been waved through as "not an
     * image" and landed on the CDN in exactly the form we are trying to
     * keep off it. A list of what may pass fails the safe way instead.
     *
     * The binary is sniffed rather than trusting the type the browser
     * announced, which is guesswork on a RAW file and a lie on a
     * renamed one.
     */
    public static function mustConvert(string $binary): bool
    {
        /** A GIF keeps its frames; a PDF is the 주보 or a church form. */
        return ! str_starts_with($binary, 'GIF8')
            && ! str_starts_with($binary, '%PDF');
    }

    /**
     * Refuse an image that would only reach the CDN as a broken file.
     *
     * A toast says what happened, and the validation error stops the
     * save on the field itself so nothing half-finished is written.
     *
     * @throws ValidationException
     */
    protected static function refuse(FileUpload $component): never
    {
        $message = '이 사진은 웹에서 보이는 형식으로 바꾸지 못했어요. 다른 파일로 다시 올려주세요.';

        Notification::make()
            ->danger()
            ->title('사진을 변환하지 못했어요')
            ->body($message)
            ->persistent()
            ->send();

        throw ValidationException::withMessages([$component->getStatePath() => $message]);
    }

    /**
     * Remove the uploaded original once it has been stored elsewhere.
     *
     * A failure here is not worth losing the save over - Livewire's own
     * sweep still catches the file within the day - so it is swallowed.
     */
    public static function discard(TemporaryUploadedFile $file): void
    {
        try {
            $file->delete();
        } catch (Throwable) {
            /** Left for Livewire's scheduled cleanup. */
        }
    }
}
