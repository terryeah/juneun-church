<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Filament\Support\SaveUploadsAsWebp;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Form schema for church documents and forms (교회 문서/서식).
 */
class DocumentForm
{
    /**
     * Configure the document form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('제목')
                    ->required()
                    ->maxLength(255),
                TextInput::make('description')
                    ->label('설명')
                    ->maxLength(255)
                    ->helperText('자료실에서 제목 아래에 한 줄로 보이는 설명입니다. 이 문서가 어떤 용도인지 짧게 적어 주세요.'),
                FileUpload::make('file_path')
                    ->label('문서 PDF')
                    ->maxSize(131072)
                    ->helperText('PDF만 올릴 수 있고, 128MB까지 됩니다. 사진이 많은 선교편지처럼 큰 문서도 그대로 올리시면 됩니다.')
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk(config('filesystems.media'))
                    ->directory('documents')
                    ->visibility('private')
                    ->required()
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                        /**
                         * Private, and under a random name. The bucket
                         * is served by the CDN: written public, a form
                         * the office hands out to the congregation
                         * answered anyone holding its address and was
                         * cached at the edge for a year, so the round
                         * trip through the site counted for nothing.
                         */
                        $path = 'documents/'.Str::uuid().'.pdf';

                        /**
                         * Handed over as a stream rather than a string.
                         * A 문서 may now be 128MB - the mission letters
                         * run to twenty-odd - and reading one into a
                         * variable would hold all of it in memory on a
                         * 1GB server that is also running MySQL.
                         */
                        $handle = fopen($file->getRealPath(), 'rb');

                        Storage::disk(config('filesystems.media'))
                            ->put($path, $handle, ['visibility' => 'private']);

                        if (is_resource($handle)) {
                            fclose($handle);
                        }

                        /** The upload does not linger on the server once it is on the CDN. */
                        SaveUploadsAsWebp::discard($file);

                        return $path;
                    }),
                DatePicker::make('published_at')
                    ->label('발행일')
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->required()
                    ->default(now()),
            ]);
    }
}
