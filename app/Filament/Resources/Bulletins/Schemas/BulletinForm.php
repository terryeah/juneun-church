<?php

namespace App\Filament\Resources\Bulletins\Schemas;

use App\Filament\Support\SaveUploadsAsWebp;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Form schema for weekly bulletins (주보).
 */
class BulletinForm
{
    /**
     * Configure the bulletin form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('제목')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('published_at')
                    ->label('발행일')
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->required()
                    ->default(now()),
                FileUpload::make('file_path')
                    ->label('주보 PDF')
                    ->maxSize(20480)
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk(config('filesystems.media'))
                    ->directory('bulletins')
                    ->visibility('private')
                    ->required()
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                        /**
                         * Private, and under a random name. A 주보
                         * carries the cell lists, the rota and the
                         * offering record, and the bucket is served by
                         * the CDN: written public, the file answered
                         * anyone holding its address and was cached at
                         * the edge for a year, so the round trip
                         * through the site counted for nothing.
                         */
                        $path = 'bulletins/'.Str::uuid().'.pdf';
                        Storage::disk(config('filesystems.media'))
                            ->put($path, (string) file_get_contents($file->getRealPath()), ['visibility' => 'private']);

                        /** The upload does not linger on the server once it is on the CDN. */
                        SaveUploadsAsWebp::discard($file);

                        return $path;
                    }),
            ]);
    }
}
