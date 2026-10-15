<?php

namespace App\Filament\Resources\Devotions\Tables;

use App\Filament\Support\Author;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DevotionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('devotion_date', 'desc')
            ->columns([
                TextColumn::make('devotion_date')
                    ->label('날짜')
                    ->date('Y-m-d (D)')
                    ->sortable(),
                TextColumn::make('title')
                    ->label('제목')
                    ->searchable()
                    ->weight(FontWeight::SemiBold)
                    ->wrap(),
                TextColumn::make('scripture')
                    ->label('본문')
                    ->searchable(),
                IconColumn::make('is_published')
                    ->label('게시')
                    ->boolean(),
                Author::column('author.name', '작성자')
                    ->visibleFrom('lg'),
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
