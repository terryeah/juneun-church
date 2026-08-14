<?php

namespace App\Filament\Resources\Sermons\Tables;

use App\Filament\Support\Author;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * The 예배 listing.
 *
 * A sermon is found by its title, who preached it and which Sunday it
 * was, so those three and the publish tick are what a phone shows. The
 * YouTube ID is a machine identifier nobody reads down a column - it is
 * still searchable from the column menu - and the passage and service
 * type are worth a column only once there is a laptop's width.
 */
class SermonsTable
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
                TextColumn::make('scripture_reference')
                    ->label('본문 말씀')
                    ->searchable()
                    ->visibleFrom('lg'),
                TextColumn::make('youtube_video_id')
                    ->label('YouTube ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('preacher')
                    ->label('설교자')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('sermon_date')
                    ->label('예배 날짜')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('serviceType.name')
                    ->label('예배 종류')
                    ->searchable()
                    ->visibleFrom('lg'),
                IconColumn::make('is_published')
                    ->label('게시')
                    ->boolean(),
                Author::column('author.name', '작성자')
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
