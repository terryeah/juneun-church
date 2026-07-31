<?php

namespace App\Filament\Resources\Albums\Schemas;

use App\Models\Album;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

/**
 * Form schema for photo albums (갤러리).
 */
class AlbumForm
{
    /**
     * Configure the album form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('앨범명')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('event_date')
                    ->label('행사 날짜')
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->required(),
                Textarea::make('description')
                    ->label('설명')
                    ->columnSpanFull(),
                FileUpload::make('cover_photo_path')
                    ->label('커버 사진')
                    ->image()
                    ->disk(config('filesystems.media'))
                    ->directory(fn (?Album $record): string => 'albums/'.($record?->slug ?? 'covers'))
                    ->visibility('public'),
                Toggle::make('is_published')
                    ->label('활성화')
                    ->default(true),
                TextInput::make('slug')
                    ->label('슬러그')
                    ->helperText('비워두면 영문 제목 또는 album-YYYYMMDD 형식으로 자동 생성됩니다.')
                    ->maxLength(255),
            ]);
    }
}
