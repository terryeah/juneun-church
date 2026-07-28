<?php

namespace App\Filament\Support;

use App\Services\WebpImageConverter;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Global file upload behaviour that stores images as WebP.
 *
 * Applied to every Filament FileUpload component: raster images in any
 * other format are converted to WebP before being written to the media
 * disk, images already in WebP are stored as they are, and non-image
 * files (for example bulletin PDFs) pass through untouched.
 */
class SaveUploadsAsWebp
{
    /**
     * Register the behaviour for all FileUpload components.
     */
    public static function register(): void
    {
        FileUpload::configureUsing(function (FileUpload $component): void {
            $component->saveUploadedFileUsing(
                static fn (FileUpload $component, TemporaryUploadedFile $file): string => static::store($component, $file),
            );
        });
    }

    /**
     * Store the uploaded file, converting images to WebP when needed.
     */
    protected static function store(FileUpload $component, TemporaryUploadedFile $file): string
    {
        $converter = app(WebpImageConverter::class);
        $binary = (string) file_get_contents($file->getRealPath());

        $directory = trim((string) $component->getDirectory(), '/');
        $prefix = $directory === '' ? '' : $directory.'/';
        $options = ['visibility' => $component->getVisibility()];
        $disk = Storage::disk($component->getDiskName());

        if (! $converter->isWebp($binary) && $converter->isConvertibleImage($binary)) {
            $converted = $converter->toWebp($binary);

            if ($converted !== null) {
                $path = $prefix.Str::uuid().'.webp';
                $disk->put($path, $converted, $options);

                return $path;
            }
        }

        $extension = $converter->isWebp($binary)
            ? 'webp'
            : strtolower($file->getClientOriginalExtension() ?: 'bin');

        $path = $prefix.Str::uuid().'.'.$extension;
        $disk->put($path, $binary, $options);

        return $path;
    }
}
