<?php

namespace App\Filament\Resources\Announcements\Tables;

use App\Models\Announcement;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * The 교회 소식 listing.
 *
 * Ten columns side by side only ever fitted a desktop, and on a phone
 * Filament stacks each one into a labelled row, so the card ran the
 * height of the screen. Three separate tick columns are now a single
 * badge that says what state the notice is in, and the reference
 * columns only appear once there is width to spare.
 */
class AnnouncementsTable
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
                TextColumn::make('states')
                    ->label('상태')
                    ->badge()
                    ->state(fn (Announcement $record): array => array_values(array_filter([
                        $record->is_published ? '게시' : '비공개',
                        $record->is_pinned ? '고정' : null,
                        $record->is_highlighted ? '하이라이트' : null,
                        $record->is_members_only ? '성도 전용' : null,
                    ])))
                    ->color(fn (string $state): string => match ($state) {
                        '게시', '성도 전용' => 'success',
                        '비공개' => 'gray',
                        default => 'warning',
                    }),
                TextColumn::make('published_at')
                    ->label('게시 일시')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                TextColumn::make('author.name')
                    ->label('작성자')
                    ->default('시스템')
                    ->sortable()
                    ->visibleFrom('lg'),
                TextColumn::make('expires_at')
                    ->label('게시 종료')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('slug')
                    ->label('슬러그')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
