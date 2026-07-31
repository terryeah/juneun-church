<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('제목')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('event_date')
                    ->label('행사일')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('event_time')
                    ->label('시작 시간')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('종료일')
                    ->date('Y-m-d')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label('종료 시간')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('location')
                    ->label('행사장')
                    ->searchable(),
                IconColumn::make('is_published')
                    ->label('게시')
                    ->boolean(),
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
