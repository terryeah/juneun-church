<?php

namespace App\Filament\Resources\Activities\Tables;

use App\Filament\Resources\Activities\Schemas\ActivityChanges;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
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
                 *
                 * The date and the time are separate elements so the
                 * stacked card can put the time on its own line. Left as
                 * one string it broke wherever it happened to run out of
                 * room - after the comma, mid-way through a date - which
                 * is what made a phone read '2026-08-05,' above
                 * '23:08:17'. The comma is drawn by CSS, so it goes when
                 * the line does.
                 */
                TextColumn::make('created_at')
                    ->label('일시')
                    ->html()
                    ->state(fn (Activity $record): HtmlString => new HtmlString(
                        '<span class="fi-datetime-date">'.e($record->created_at->format(AppServiceProvider::DATE_FORMAT)).'</span>'
                        .'<span class="fi-datetime-time">'.e($record->created_at->format('H:i:s')).'</span>',
                    ))
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
                /**
                 * Who did it. Only accounts that appear in the log are
                 * offered, so the list is the people who have actually
                 * done something rather than the whole roster, and an
                 * account that has since been closed keeps the name the
                 * 사용자 column gives it.
                 */
                SelectFilter::make('causer_id')
                    ->label('사용자')
                    ->options(fn (): array => self::causerOptions())
                    ->searchable(),
                /**
                 * Rows the log cannot put a name to read as 시스템: a
                 * failed sign-in, a visit by somebody not signed in, a
                 * scheduled command, anything an exempt account does,
                 * and anything left behind by an account since closed.
                 *
                 * They outnumber the rest and answer a different question
                 * from "who changed what", so they are off unless asked
                 * for. The filter indicator says so on the toolbar, which
                 * is what stops a hidden default reading as an empty log.
                 */
                TernaryFilter::make('system')
                    ->label('시스템 기록')
                    ->placeholder('사람 + 시스템 모두')
                    ->trueLabel('시스템 기록만')
                    ->falseLabel('사람이 한 기록만')
                    ->queries(
                        true: fn (Builder $query): Builder => self::scopeToNamedCausers($query, false),
                        false: fn (Builder $query): Builder => self::scopeToNamedCausers($query, true),
                        blank: fn (Builder $query): Builder => $query,
                    )
                    ->default(false),
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
     * The accounts that appear in the log, named.
     *
     * Only accounts that still exist: an id left behind by a closed one
     * has no name to offer and its rows now sit under 시스템 anyway.
     *
     * @return array<int, string>
     */
    private static function causerOptions(): array
    {
        return User::query()
            ->whereKey(Activity::query()->whereNotNull('causer_id')->distinct()->pluck('causer_id'))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * Who a row belongs to, or null for anything the log cannot name.
     *
     * Closing someone's 사이트 계정 deletes the account outright and
     * nothing ties the log back to it, so the rows that person left
     * behind carry an id that resolves to nobody. They read 시스템 with
     * the rest of the unattributable rows and are hidden with them,
     * rather than showing a '삭제된 계정 #8' nobody can act on.
     *
     * What that costs is the difference between "no one was signed in"
     * and "somebody whose account is gone", which the log no longer
     * draws. The id is still in the column for anyone who needs it.
     */
    private static function causer(Activity $record): ?string
    {
        return $record->causer?->name;
    }

    /**
     * Rows the log can put a name to.
     *
     * A causer_id pointing at an account that no longer exists is not
     * one of them, so the check is for a matching user rather than for
     * the column being filled.
     *
     * @param  Builder<Activity>  $query
     * @return Builder<Activity>
     */
    private static function scopeToNamedCausers(Builder $query, bool $named): Builder
    {
        $users = User::query()->select('id');

        return $named
            ? $query->whereIn('causer_id', $users)
            : $query->where(fn (Builder $inner): Builder => $inner
                ->whereNull('causer_id')
                ->orWhereNotIn('causer_id', $users));
    }
}
