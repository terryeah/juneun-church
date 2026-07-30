<?php

namespace App\Filament\Resources\Photos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Models\Photo;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PhotosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail_path')
                    ->label('썸네일')
                    ->disk(config('filesystems.media'))
                    ->state(fn (Photo $record): string => $record->thumbnail_path ?? $record->path)
                    ->imageHeight(44),
                TextColumn::make('album.title')
                    ->label('앨범')
                    ->searchable(),
                TextColumn::make('filename')
                    ->label('파일명')
                    ->searchable(),
                TextColumn::make('file_size')
                    ->label('파일 크기')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1048576, 2).' MB' : '-')
                    ->sortable(),
                IconColumn::make('featured_in_slider')
                    ->label('홈 슬라이더')
                    ->boolean(),
                TextColumn::make('path')
                    ->label('경로')
                    ->searchable(),
                TextColumn::make('uploader.name')
                    ->label('업로더')
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
            ->reorderable('sort_order')
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
            ])
            ->emptyStateActions([
                CreateAction::make()->label('업로드'),
            ]);
    }
}
