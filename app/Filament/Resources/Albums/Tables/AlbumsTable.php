<?php

namespace App\Filament\Resources\Albums\Tables;

use App\Models\Album;
use App\Models\Photo;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

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
                    ->imageHeight(44),
                TextColumn::make('title')
                    ->label('앨범명')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('슬러그')
                    ->searchable(),
                TextColumn::make('event_date')
                    ->label('행사 날짜')
                    ->date('Y-m-d')
                    ->sortable(),
                ToggleColumn::make('is_published')
                    ->label('활성화'),
                TextColumn::make('author.name')
                    ->label('작성자')
                    ->default('시스템')
                    ->sortable(),
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
