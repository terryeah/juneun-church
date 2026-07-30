<?php

namespace App\Filament\Resources\Sermons\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

/**
 * Form schema for worship recordings (예배).
 */
class SermonForm
{
    /**
     * Configure the sermon form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('제목')
                    ->required()
                    ->maxLength(255),
                TextInput::make('scripture_reference')
                    ->label('본문 말씀')
                    ->maxLength(255),
                TextInput::make('youtube_video_id')
                    ->label('YouTube 영상 ID')
                    ->required()
                    ->regex('/^[A-Za-z0-9_-]{11}$/')
                    ->helperText('영상 주소의 v= 뒤 11자리 (예: dQw4w9WgXcQ)'),
                Select::make('service_type_id')
                    ->label('예배 종류')
                    ->relationship('serviceType', 'name')
                    ->required(),
                TextInput::make('preacher')
                    ->label('설교자')
                    ->maxLength(255),
                DatePicker::make('sermon_date')
                    ->label('예배 날짜')
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->required(),
                Textarea::make('description')
                    ->label('설명')
                    ->rows(9),
                FileUpload::make('thumbnail_path')
                    ->label('썸네일')
                    ->image()
                    ->disk(config('filesystems.media'))
                    ->directory('youtube')
                    ->visibility('public')
                    ->saveUploadedFileUsing(function (\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file): string {
                        $binary = (string) file_get_contents($file->getRealPath());
                        $path = 'youtube/thumbnail-'.now('Australia/Brisbane')->format('Y-m-d-His');
                        \Illuminate\Support\Facades\Storage::disk(config('filesystems.media'))->put(
                            $path,
                            \App\Filament\Support\SaveUploadsAsWebp::toWebp($binary) ?? $binary,
                            ['ContentType' => 'image/webp', 'CacheControl' => 'public, max-age=31536000, immutable'],
                        );

                        return $path;
                    }),
                Toggle::make('is_published')
                    ->label('게시')
                    ->default(true),
            ]);
    }
}
