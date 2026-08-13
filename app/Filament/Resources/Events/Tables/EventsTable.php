<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * The 교회 행사 listing.
 *
 * Five date and time columns side by side reduced the phone card to a
 * column of dashes, because most events run for an hour on one day and
 * only fill the first of them. What identifies an event on a phone is
 * its name, the day, where it is and whether it is live; the start and
 * end times, the end date and the author are all still there the moment
 * there is a laptop's width to put them in.
 */
class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('제목')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->wrap(),
                TextColumn::make('event_date')
                    ->label('행사일')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('event_time')
                    ->label('시작 시간')
                    ->time('H:i')
                    ->sortable()
                    ->placeholder('-')
                    ->visibleFrom('lg'),
                TextColumn::make('end_date')
                    ->label('종료일')
                    ->date('Y-m-d')
                    ->sortable()
                    ->placeholder('-')
                    ->visibleFrom('lg'),
                TextColumn::make('end_time')
                    ->label('종료 시간')
                    ->time('H:i')
                    ->sortable()
                    ->placeholder('-')
                    ->visibleFrom('lg'),
                TextColumn::make('location')
                    ->label('행사장')
                    ->searchable()
                    ->placeholder('-'),
                IconColumn::make('is_published')
                    ->label('게시')
                    ->boolean(),
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
