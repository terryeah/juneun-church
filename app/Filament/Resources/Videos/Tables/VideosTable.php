<?php

namespace App\Filament\Resources\Videos\Tables;

use App\Models\Album;
use App\Models\Video;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Table configuration for videos.
 *
 * The still leads, because that is how somebody recognises a video; the
 * identifier is eleven characters nobody reads.
 */
class VideosTable
{
    /**
     * Configure the video table.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('youtube_id')
                    ->label('영상')
                    ->getStateUsing(fn (Video $record): string => $record->thumbnailUrl())
                    ->extraCellAttributes(['class' => 'stacked-span-full stacked-hide-label stacked-media'])
                    ->height(48)
                    ->width(85),
                TextColumn::make('title')
                    ->label('제목')
                    ->weight(FontWeight::SemiBold)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('album.title')
                    ->label('앨범')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label('순서')
                    ->sortable()
                    ->visibleFrom('lg'),
                TextColumn::make('created_at')
                    ->label('추가일')
                    ->dateTime()
                    ->sortable()
                    ->visibleFrom('lg'),
            ])
            ->filters([
                SelectFilter::make('album_id')
                    ->label('앨범')
                    ->relationship('album', 'title', fn ($query) => $query->ofType(Album::TYPE_VIDEO))
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
