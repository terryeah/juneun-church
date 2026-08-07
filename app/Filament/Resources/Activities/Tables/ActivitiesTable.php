<?php

namespace App\Filament\Resources\Activities\Tables;

use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

/**
 * Table configuration for the activity log browser.
 */
class ActivitiesTable
{
    /**
     * Configure the activity log table.
     */
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                /**
                 * Pinned so the column keeps the same width from page
                 * to page. Left to size itself, an auto-laid-out table
                 * hands it whatever slack the other columns leave, and
                 * that slack changes with the content of each page.
                 */
                TextColumn::make('created_at')
                    ->label('일시')
                    ->dateTime()
                    ->width('1%')
                    ->extraCellAttributes(['class' => 'whitespace-nowrap'])
                    ->sortable(),
                TextColumn::make('causer.name')
                    ->label('사용자')
                    ->placeholder('시스템')
                    ->searchable(),
                TextColumn::make('event')
                    ->label('동작')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'created', 'login' => 'success',
                        'updated' => 'warning',
                        'deleted', 'failed_login' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('subject_type')
                    ->label('대상')
                    ->formatStateUsing(fn (?string $state, Activity $record): string => $state
                        ? class_basename($state).' #'.$record->subject_id
                        : '-'),
                TextColumn::make('properties.ip')
                    ->label('IP 주소')
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('동작')
                    ->options([
                        'created' => 'created',
                        'updated' => 'updated',
                        'deleted' => 'deleted',
                        'login' => 'login',
                        'logout' => 'logout',
                        'failed_login' => 'failed_login',
                    ]),
                SelectFilter::make('log_name')
                    ->label('로그 종류')
                    ->options([
                        'default' => 'content',
                        'auth' => 'auth',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('일시')
                            ->dateTime(),
                        TextEntry::make('causer.name')
                            ->label('사용자')
                            ->placeholder('시스템'),
                        TextEntry::make('description')
                            ->label('내용'),
                        TextEntry::make('id')
                            ->label('변경 사항')
                            ->formatStateUsing(fn (Activity $record): string => json_encode(
                                $record->attribute_changes->isNotEmpty()
                                    ? $record->attribute_changes
                                    : $record->properties,
                                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                            ))
                            ->markdown(false)
                            ->extraAttributes(['class' => 'font-mono whitespace-pre-wrap text-xs']),
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(25)
            ->poll(null);
    }
}
