<?php

namespace App\Filament\Resources\StaffMembers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StaffMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('이름')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('position.name')
                    ->label('직분')
                    ->searchable(),
                TextColumn::make('department')
                    ->label('부서 / 사역')
                    ->searchable(),
                ImageColumn::make('photo')
                    ->label('사진')
                    ->disk(config('filesystems.media'))
                    ->circular(),
                TextColumn::make('email')
                    ->label('이메일')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('전화번호')
                    ->searchable(),
                IconColumn::make('is_published')
                    ->label('게시')
                    ->boolean(),
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
            ->defaultSort('sort_order')
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
            ]);
    }
}
