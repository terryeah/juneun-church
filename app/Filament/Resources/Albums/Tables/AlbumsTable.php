<?php

namespace App\Filament\Resources\Albums\Tables;

use App\Models\Album;
use App\Models\Photo;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

/**
 * The 갤러리 album listing.
 *
 * A phone gets the four things that identify an album - its cover, its
 * name, when the event was and whether it is live - and nothing else.
 * The two publish switches are left as switches rather than folded into
 * one badge: flipping 활성화 from the list is the whole point of them,
 * and a badge cannot be flipped. 성도 전용 is the rarer of the two, so
 * it waits for a laptop along with the author, and the slug is
 * reference material behind the column menu.
 *
 * The cover leads the card on a phone rather than sitting in a grid
 * cell at thumbnail size. Stacking every column into an equal labelled
 * cell puts a 44px picture beside a line of text, which is exactly
 * backwards for a screen whose whole subject is the picture; the three
 * stacked- classes hand it the full width and drop the label it does
 * not need. Above the breakpoint nothing changes.
 */
class AlbumsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover_photo_path')
                    ->label('커버 사진')
                    ->disk(config('filesystems.media'))
                    ->state(function (Album $record): ?string {
                        if (! $record->cover_photo_path) {
                            return null;
                        }

                        $photo = Photo::query()->where('path', $record->cover_photo_path)->first();

                        return $photo?->thumbnail_path ?? $record->cover_photo_path;
                    })
                    ->imageHeight(44)
                    ->extraCellAttributes(['class' => 'stacked-span-full stacked-hide-label stacked-media']),
                TextColumn::make('title')
                    ->label('앨범명')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->wrap(),
                TextColumn::make('slug')
                    ->label('슬러그')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('event_date')
                    ->label('행사 날짜')
                    ->date('Y-m-d')
                    ->sortable()
                    ->placeholder('-'),
                ToggleColumn::make('is_published')
                    ->label('활성화'),
                ToggleColumn::make('is_members_only')
                    ->label('성도 전용'),
                TextColumn::make('author.name')
                    ->label('작성자')
                    ->default('시스템')
                    ->sortable()
                    ->visibleFrom('lg'),
                TextColumn::make('created_at')
                    ->label('생성일')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('수정일')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
