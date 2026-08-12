<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Filament\Support\SaveUploadsAsWebp;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                    ->maxSize(20480)
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk(config('filesystems.media'))
                    ->directory('documents')
                    ->visibility('public')
                    ->required()
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                        /**
                         * A random name, because the file itself is not
                         * behind the login - only the listing is. The
                         * name used to be the upload's date and time to
                         * the second, which is one day's worth of
                         * guesses for anyone who knows a 주보 goes up on
                         * a Sunday, and a 주보 carries the cell lists,
                         * the rota and the offering record.
                         */
                        $path = 'documents/'.Str::uuid().'.pdf';
                        Storage::disk(config('filesystems.media'))
                            ->put($path, (string) file_get_contents($file->getRealPath()), ['visibility' => 'public']);

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
                Toggle::make('is_members_only')
                    ->label('성도 전용')
                    ->default(true)
                    ->helperText('끄면 로그인하지 않은 방문자도 이 문서를 내려받을 수 있습니다. 성도의 정보가 들어 있지 않은 문서에만 꺼 주세요.'),
            ]);
    }
}
