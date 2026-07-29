<?php

namespace App\Filament\Resources\Announcements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('제목')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('슬러그')
                    ->searchable(),
                IconColumn::make('is_published')
                    ->label('게시')
                    ->boolean(),
                IconColumn::make('is_pinned')
                    ->label('상단 고정')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label('게시 일시')
                    ->dateTime('Y-m-d, h:i:s A')
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->label('게시 종료')
                    ->dateTime('Y-m-d, h:i:s A')
                    ->sortable(),
                TextColumn::make('created_by')
                    ->label('작성자')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('author.name')
                    ->label('작성자')
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
            ]);
    }
}
