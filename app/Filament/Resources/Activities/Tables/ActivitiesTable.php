<?php

namespace App\Filament\Resources\Activities\Tables;

use App\Filament\Resources\Activities\Schemas\ActivityChanges;
use App\Providers\AppServiceProvider;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
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
     * Badge colour for a recorded event. Anything that removes or fails
     * reads as danger, anything that creates or admits reads as success,
     * and a change sits between them.
     */
    public static function eventColour(?string $event): string
    {
        return match ($event) {
            'created', 'login' => 'success',
            'updated' => 'warning',
            'deleted', 'failed_login' => 'danger',
            default => 'gray',
        };
    }

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
                    ->dateTime(AppServiceProvider::DATE_TIME_FORMAT.':s')
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
                    ->formatStateUsing(fn (?string $state): ?string => ActivityChanges::event($state))
                    ->color(fn (?string $state): string => self::eventColour($state)),
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
                    ->state(fn (Activity $record): string => ActivityChanges::subject($record) ?? '-'),
                TextColumn::make('properties.ip')
                    ->label('IP 주소')
                    ->placeholder('-')
                    ->visibleFrom('lg'),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('동작')
                    ->options(ActivityChanges::EVENTS),
                /**
                 * Page visits outnumber everything else by a wide
                 * margin, so this filter is how a developer gets back
                 * to the record of what people actually changed.
                 */
                SelectFilter::make('log_name')
                    ->label('로그 종류')
                    ->options([
                        'default' => '내용 변경',
                        'auth' => '로그인',
                        'page' => '페이지 열람',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading('활동 기록')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('일시')
                            ->dateTime(AppServiceProvider::DATE_TIME_FORMAT.':s'),
                        TextEntry::make('causer.name')
                            ->label('사용자')
                            ->state(fn (Activity $record): ?string => self::causer($record))
                            ->placeholder('시스템'),
                        TextEntry::make('event')
                            ->label('동작')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): ?string => ActivityChanges::event($state))
                            ->color(fn (?string $state): string => self::eventColour($state)),
                        TextEntry::make('subject_type')
                            ->label('대상')
                            ->state(fn (Activity $record): ?string => ActivityChanges::subject($record))
                            ->placeholder('-'),
                        /**
                         * Hidden when the stored description is just the
                         * event name, which is what a model change logs
                         * - it read 'updated' directly beneath a badge
                         * already saying 수정.
                         */
                        TextEntry::make('description')
                            ->label('내용')
                            ->visible(fn (Activity $record): bool => ActivityChanges::hasDescription($record))
                            ->columnSpanFull(),
                        /**
                         * The changed columns as a table rather than as
                         * the pretty-printed JSON this used to be. The
                         * blob was accurate and unreadable: raw column
                         * names, bare foreign keys, and the before and
                         * after values in two separate objects the
                         * reader had to line up by eye.
                         */
                        /**
                         * Entry labels are set rather than hidden. Below
                         * the repeatable's own breakpoint Filament drops
                         * the header row and stacks each row as a block,
                         * captioning every cell with its entry label -
                         * so hiddenLabel() leaves a phone showing four
                         * bare values with nothing to say which column
                         * any of them came from.
                         */
                        RepeatableEntry::make('changes')
                            ->label('변경 사항')
                            ->state(fn (Activity $record): array => ActivityChanges::rows($record))
                            ->table([
                                TableColumn::make('항목'),
                                TableColumn::make('이전'),
                                TableColumn::make('이후'),
                            ])
                            ->schema([
                                TextEntry::make('field')
                                    ->label('항목')
                                    ->weight(FontWeight::Medium),
                                TextEntry::make('before')
                                    ->label('이전')
                                    ->placeholder('-')
                                    ->color('gray'),
                                TextEntry::make('after')
                                    ->label('이후')
                                    ->placeholder('-'),
                            ])
                            ->hidden(fn (Activity $record): bool => ActivityChanges::rows($record) === [])
                            ->columnSpanFull(),
                        RepeatableEntry::make('context')
                            ->label('접속 정보')
                            ->state(fn (Activity $record): array => ActivityChanges::context($record))
                            ->table([
                                TableColumn::make('항목'),
                                TableColumn::make('값'),
                            ])
                            ->schema([
                                TextEntry::make('field')
                                    ->label('항목')
                                    ->weight(FontWeight::Medium),
                                TextEntry::make('value')
                                    ->label('값')
                                    ->placeholder('-'),
                            ])
                            ->hidden(fn (Activity $record): bool => ActivityChanges::context($record) === [])
                            ->columnSpanFull(),
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
