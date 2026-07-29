<?php

namespace App\Filament\Resources\Photos\Schemas;

use App\Models\Album;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
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
                    ->live()
                    ->required(),
                TextInput::make('sort_order')
                    ->label('정렬 순서')
                    ->numeric()
                    ->default(0),
                FileUpload::make('path')
                    ->label('사진')
                    ->image()
                    ->disk(config('filesystems.media'))
                    ->directory(fn (Get $get): string => 'albums/'.(Album::query()->find($get('album_id'))?->slug ?? 'unsorted'))
                    ->visibility('public')
                    ->required(),
            ]);
    }
}
