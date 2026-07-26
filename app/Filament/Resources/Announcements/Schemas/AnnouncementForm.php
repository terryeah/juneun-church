<?php

namespace App\Filament\Resources\Announcements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

/**
 * Form schema for church announcements (교회 소식).
 */
class AnnouncementForm
{
    /**
     * Configure the announcement form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('제목')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                RichEditor::make('content')
                    ->label('내용')
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('featured_image')
                    ->label('대표 이미지')
                    ->image()
                    ->disk(config('filesystems.media'))
                    ->directory('announcements')
                    ->visibility('public')
                    ->columnSpanFull(),
                Toggle::make('is_published')
                    ->label('게시')
                    ->default(true),
                Toggle::make('is_pinned')
                    ->label('상단 고정'),
                DateTimePicker::make('published_at')
                    ->label('게시 일시')
                    ->default(now()),
                DateTimePicker::make('expires_at')
                    ->label('게시 종료 일시')
                    ->helperText('비워두면 계속 게시됩니다.'),
                TextInput::make('slug')
                    ->label('슬러그')
                    ->helperText('비워두면 제목으로 자동 생성됩니다.')
                    ->maxLength(255),
            ]);
    }
}
