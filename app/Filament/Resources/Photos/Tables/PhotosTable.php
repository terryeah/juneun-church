<?php

namespace App\Filament\Resources\Photos\Tables;

use Filament\Actions\BulkActionGroup;
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
                TextColumn::make('thumbnail_path')
                    ->label('썸네일 경로')
                    ->searchable(),
                TextColumn::make('width')
                    ->label('가로')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('height')
                    ->label('세로')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('file_size')
                    ->label('파일 크기')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('caption')
                    ->label('설명')
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label('정렬 순서')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('uploaded_by')
                    ->label('업로더')
                    ->numeric()
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
            ]);
    }
}
