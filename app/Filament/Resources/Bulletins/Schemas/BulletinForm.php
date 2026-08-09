<?php

namespace App\Filament\Resources\Bulletins\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
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
                    ->visibility('public')
                    ->required()
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file): string {
                        /** Bulletins are always stored as bulletin-{upload date-time}.pdf */
                        $path = 'bulletins/bulletin-'.now('Australia/Brisbane')->format('Y-m-d-His').'.pdf';
                        Storage::disk(config('filesystems.media'))
                            ->put($path, (string) file_get_contents($file->getRealPath()), ['visibility' => 'public']);

                        return $path;
                    }),
                Toggle::make('is_members_only')
                    ->label('성도 전용')
                    ->default(true)
                    ->helperText('주보에는 셀 편성, 섬김이 명단, 헌금 내역 등 성도의 정보가 들어갑니다. 끄면 로그인하지 않은 방문자도 PDF를 내려받을 수 있으니, 공개해도 괜찮은 주보에만 꺼 주세요.'),
            ]);
    }
}
