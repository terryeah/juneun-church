<?php

namespace App\Filament\Resources\Photos\Schemas;

use App\Models\Album;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                TextInput::make('filename')
                    ->label('파일 이름')
                    ->readOnly()
                    ->copyable(copyMessage: '파일 이름을 복사했어요')
                    ->helperText('홈 화면 대표 사진으로 쓰려면 오른쪽 버튼으로 복사해서 사이트 설정에 붙여넣으세요.')
                    ->visibleOn('edit'),
                FileUpload::make('path')
                    ->label('사진')
                    ->image()
                    ->maxSize(15360)
                    ->disk(config('filesystems.media'))
                    ->directory(fn (Get $get): string => 'albums/'.(Album::query()->find($get('album_id'))?->slug ?? 'unsorted'))
                    ->visibility('public')
                    ->required(),
                Toggle::make('featured_in_slider')
                    ->label('홈 슬라이더에 표시')
                    ->helperText('최대 10장까지 선택할 수 있습니다.')
                    ->columnSpanFull(),
            ]);
    }
}
