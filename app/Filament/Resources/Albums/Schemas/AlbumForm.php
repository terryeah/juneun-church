<?php

namespace App\Filament\Resources\Albums\Schemas;

use App\Models\Album;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
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
                /**
                 * Fixed once the album has something in it: switching
                 * kind would leave the contents behind in a screen that
                 * no longer lists them.
                 */
                Select::make('type')
                    ->label('종류')
                    ->options(Album::TYPES)
                    ->default(Album::TYPE_PHOTO)
                    ->required()
                    ->live()
                    ->selectablePlaceholder(false)
                    ->disabled(fn (?Album $record): bool => $record !== null
                        && ($record->photos()->exists() || $record->videos()->exists()))
                    ->helperText(fn (?Album $record): ?string => $record !== null
                        && ($record->photos()->exists() || $record->videos()->exists())
                            ? '이미 담긴 것이 있어 종류를 바꿀 수 없습니다. 비우면 바꿀 수 있습니다.'
                            : '사진 앨범에는 사진을, 동영상 앨범에는 유튜브 영상을 담습니다.'),
                TextInput::make('slug')
                    ->label('슬러그')
                    ->helperText('비워두면 영문 제목 또는 album-YYYYMMDD 형식으로 자동 생성됩니다.')
                    ->maxLength(255),
                DatePicker::make('event_date')
                    ->label('행사 날짜')
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('설명')
                    ->rows(9),
                /**
                 * A video album takes its cover from YouTube's own
                 * still, so there is nothing to upload and nothing to
                 * keep in step.
                 */
                FileUpload::make('cover_photo_path')
                    ->label('커버 사진')
                    ->visible(fn (Get $get): bool => $get('type') !== Album::TYPE_VIDEO)
                    ->image()
                    ->maxSize(15360)
                    ->disk(config('filesystems.media'))
                    ->directory(fn (?Album $record): string => 'albums/'.($record?->slug ?? 'covers'))
                    ->visibility('public'),
                Toggle::make('is_published')
                    ->label('활성화')
                    ->default(true)
                    ->live()
                    ->columnSpanFull(),
                /**
                 * Turning this off on a video album is not the same
                 * decision as turning it off on a photo album, and the
                 * form has to say so. A photograph taken down is taken
                 * down; a YouTube identifier, once it has been on a
                 * public page, cannot be recalled - the video is
                 * unlisted, not private, so the link is the whole lock.
                 */
                Toggle::make('is_members_only')
                    ->label('성도 전용')
                    ->helperText(fn (Get $get): string => $get('type') === Album::TYPE_VIDEO
                        ? '이 앨범의 영상은 유튜브에서 목록에 안 뜨고 주소를 아는 사람만 볼 수 있는 영상입니다. 이 스위치를 끄면 영상 주소가 공개 페이지에 실리고, 그때부터는 주소를 아는 누구나 볼 수 있게 됩니다. 되돌릴 수 없습니다.'
                        : '켜면 앨범은 목록에 성도 전용 표시와 함께 남고, 로그인한 성도만 열어서 사진을 볼 수 있습니다. 로그인하지 않은 방문자에게는 앨범 자체가 보이지 않습니다.')
                    ->visible(fn (Get $get): bool => (bool) $get('is_published'))
                    ->columnSpanFull(),
            ]);
    }
}
