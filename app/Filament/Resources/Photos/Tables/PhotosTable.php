<?php

namespace App\Filament\Resources\Photos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PhotosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('album.title')
                    ->label('앨범')
                    ->searchable(),
                TextColumn::make('filename')
                    ->label('파일명')
                    ->searchable(),
                TextColumn::make('original_filename')
                    ->label('원본 파일명')
                    ->searchable(),
                TextColumn::make('path')
                    ->label('경로')
                    ->searchable(),
                TextColumn::make('file_size')
                    ->label('파일 크기')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1048576, 2).' MB' : '-')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('정렬 순서')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('uploader.name')
                    ->label('업로더')
                    ->default('시스템')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('생성일')
                    ->dateTime('Y-m-d, h:i:s A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('수정일')
                    ->dateTime('Y-m-d, h:i:s A')
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
            ])
            ->emptyStateActions([
                CreateAction::make()->label('업로드'),
            ]);
    }
}
