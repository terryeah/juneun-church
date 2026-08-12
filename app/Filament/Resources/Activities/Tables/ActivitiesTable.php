<?php

namespace App\Filament\Resources\Activities\Tables;

use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;

/**
 * Table configuration for the activity log browser.
 *
 * A log row answers who did what to which record and when, so those
 * four stay on the phone. The IP address is only ever consulted after
 * the fact, and the view modal prints it in full alongside the rest of
 * the stored properties, so it waits for a laptop.
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
                    ->state(fn (Activity $record): ?string => self::causer($record))
                    ->placeholder('시스템')
                    ->weight(FontWeight::SemiBold)
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
                /**
                 * What the row is about: the record that was changed,
                 * or - for a page opening, which has no record - the
                 * page itself.
                 *
                 * A visit showed nothing here at all. The path lives in
                 * the description, which this table does not carry, so
                 * the one thing those rows exist to record could only
                 * be read by opening them one at a time.
                 *
                 * Built with state() rather than formatStateUsing(),
                 * because Filament skips the formatter when the column
                 * is empty and draws the placeholder instead - which is
                 * every row without a subject, and why the '-' this
                 * column was written to show never appeared either.
                 */
                TextColumn::make('subject_type')
                    ->label('대상')
                    ->state(fn (Activity $record): string => match (true) {
                        filled($record->subject_type) => class_basename($record->subject_type).' #'.$record->subject_id,
                        $record->log_name === 'page' => (string) $record->description,
                        default => '-',
                    }),
                TextColumn::make('properties.ip')
                    ->label('IP 주소')
                    ->placeholder('-')
                    ->visibleFrom('lg'),
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
                        'visited' => 'visited',
                    ]),
                /**
                 * Page visits outnumber everything else by a wide
                 * margin, so this filter is how a developer gets back
                 * to the record of what people actually changed.
                 */
                SelectFilter::make('log_name')
                    ->label('로그 종류')
                    ->options([
                        'default' => 'content',
                        'auth' => 'auth',
                        'page' => 'page',
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
                            ->state(fn (Activity $record): ?string => self::causer($record))
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
            ->poll(null);
    }

    /**
     * Who a row belongs to, including accounts that no longer exist.
     *
     * Closing someone's 사이트 계정 deletes the account outright, and
     * nothing ties the log to it, so every row that person left behind
     * would fall back to the '시스템' placeholder - reading as though
     * the site had done it rather than a person. The id is still in the
     * column, so it is shown instead, and '시스템' goes back to meaning
     * what it says: no one was signed in, as with a failed sign-in.
     */
    private static function causer(Activity $record): ?string
    {
        return $record->causer?->name
            ?? ($record->causer_id ? '삭제된 계정 #'.$record->causer_id : null);
    }
}
