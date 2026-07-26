<?php

namespace App\Filament\Resources\Bulletins\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

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
                    ->required()
                    ->default(now()),
                FileUpload::make('file_path')
                    ->label('주보 PDF')
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk(config('filesystems.media'))
                    ->directory('bulletins')
                    ->visibility('public')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
