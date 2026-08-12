<?php

namespace App\Filament\Resources\Photos\Schemas;

use App\Models\Album;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

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
                /**
                 * Photo albums only. A photograph put into a video
                 * album is invisible - that album's page lists videos -
                 * and it also locks the album's 종류, because the album
                 * form refuses to change kind once anything is inside.
                 */
                Select::make('album_id')
                    ->label('앨범')
                    ->relationship(
                        'album',
                        'title',
                        fn (Builder $query): Builder => $query->ofType(Album::TYPE_PHOTO),
                    )
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
                    /**
                     * Camera RAW is accepted as well as the web formats.
                     * Most RAW files are TIFF underneath and so already
                     * satisfy image/*, but a browser that names the
                     * maker's own type instead is listed here too.
                     */
                    ->acceptedFileTypes([
                        'image/*',
                        'image/x-adobe-dng',
                        'image/x-canon-cr2',
                        'image/x-canon-cr3',
                        'image/x-nikon-nef',
                        'image/x-sony-arw',
                        'image/x-fuji-raf',
                        'image/x-panasonic-rw2',
                        'image/x-olympus-orf',
                    ])
                    /** 64MB, which the server is configured to accept. */
                    ->maxSize(65536)
                    ->helperText('휴대폰 사진과 카메라 RAW 파일을 올릴 수 있어요. 올리면 자동으로 WebP로 바뀌고, 원본은 서버에 남지 않아요. 한 장에 64MB까지예요.')
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
