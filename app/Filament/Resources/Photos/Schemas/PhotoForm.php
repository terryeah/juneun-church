<?php

namespace App\Filament\Resources\Photos\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

/**
 * Form schema for individual gallery photographs.
 *
 * The upload is stored on the media disk; filename metadata is filled
 * in by the create page after the file has been persisted.
 */
class PhotoForm
{
    /**
     * Configure the photo form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('album_id')
                    ->label('앨범')
                    ->relationship('album', 'title')
                    ->required(),
                FileUpload::make('path')
                    ->label('사진')
                    ->image()
                    ->disk(config('filesystems.media'))
                    ->directory('gallery')
                    ->visibility('public')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('caption')
                    ->label('설명')
                    ->maxLength(255),
                TextInput::make('sort_order')
                    ->label('정렬 순서')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
