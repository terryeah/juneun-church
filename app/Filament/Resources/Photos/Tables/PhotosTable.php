<?php

namespace App\Filament\Resources\Photos\Tables;

use App\Filament\Support\Author;
use App\Models\Photo;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * The 사진 listing.
 *
 * On a phone the photograph leads the card at full width instead of
 * sitting in a grid cell at thumbnail size, which put the picture -
 * the only thing anyone is scanning for here - at 44px beside three
 * wrapped lines of UUID. The filename is a machine identifier and now
 * waits behind the column menu; the edit screen has a copy button for
 * the one time it is needed.
 */
class PhotosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail_path')
                    ->label('썸네일')
                    /**
                     * Handed a finished URL rather than a disk and a
                     * path. Given a path, Filament resolves the media
                     * disk to build the address - and resolving R2
                     * constructs an S3 client, 17 MB of AWS SDK, on
                     * every render of this table including every search
                     * keystroke and every toggle. Given a URL it returns
                     * it untouched. 동영상 already worked this way.
                     */
                    ->state(fn (Photo $record): string => $record->thumbnailUrl())
                    ->imageHeight(44)
                    ->extraCellAttributes(['class' => 'stacked-span-full stacked-hide-label stacked-media']),
                TextColumn::make('album.title')
                    ->label('앨범')
                    ->searchable(),
                TextColumn::make('filename')
                    ->label('파일명')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('file_size')
                    ->label('파일 크기')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1048576, 2).' MB' : '-')
                    ->sortable()
                    ->visibleFrom('lg'),
                IconColumn::make('featured_in_slider')
                    ->label('홈 슬라이더')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('수정일')
                    ->dateTime()
                    ->sortable()
                    ->visibleFrom('lg'),
                Author::column('uploader.name', '업로더')
                    ->visibleFrom('lg'),
                TextColumn::make('created_at')
                    ->label('생성일')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('path')
                    ->label('경로')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make()->label('업로드'),
            ]);
    }
}
