<?php

namespace App\Filament\Resources\Announcements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Flex;
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
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('슬러그')
                    ->helperText('비워두면 영문 제목 또는 news-YYYYMMDD 형식으로 자동 생성됩니다.')
                    ->maxLength(255),
                RichEditor::make('content')
                    ->label('내용')
                    ->required(),
                FileUpload::make('featured_image')
                    ->label('대표 이미지')
                    ->image()
                    ->disk(config('filesystems.media'))
                    ->directory('announcements')
                    ->visibility('public'),
                Flex::make([
                    Toggle::make('is_published')
                        ->label('게시')
                        ->default(true)
                        ->grow(false),
                    Toggle::make('is_pinned')
                        ->label('상단 고정')
                        ->grow(false),
                ])
                    ->columnSpanFull(),
                DateTimePicker::make('published_at')
                    ->label('게시 일시')
                    ->native(false)
                    ->displayFormat('Y-m-d, H:i:s')
                    ->seconds(true)
                    ->default(now()),
                DateTimePicker::make('expires_at')
                    ->label('게시 종료 일시')
                    ->native(false)
                    ->displayFormat('Y-m-d, H:i:s')
                    ->seconds(true)
                    ->helperText('비워두면 계속 게시됩니다.'),
            ]);
    }
}
