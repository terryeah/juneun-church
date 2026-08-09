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
                    ->maxSize(15360)
                    ->disk(config('filesystems.media'))
                    ->directory('announcements')
                    ->visibility('public'),
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
                Flex::make([
                    Toggle::make('is_published')
                        ->label('게시')
                        ->default(true)
                        ->grow(false),
                    Toggle::make('is_pinned')
                        ->label('상단 고정')
                        ->grow(false),
                    Toggle::make('is_highlighted')
                        ->label('하이라이트')
                        ->grow(false),
                    Toggle::make('is_members_only')
                        ->label('성도 전용')
                        ->helperText('켜면 로그인한 성도에게만 보이고, 로그인하지 않은 방문자에게는 제목도 내용도 보이지 않습니다. 새가족 소개, 셀 배정 등 성도의 이름이 들어가는 소식은 반드시 켜 주세요.')
                        ->grow(false),
                ])
                    ->columnSpanFull(),
            ]);
    }
}
