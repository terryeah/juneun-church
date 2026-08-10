<?php

namespace App\Filament\Resources\Members\Tables;

use App\Models\Member;
use App\Models\Ministry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Roster listing. Column order matters: the mobile stylesheet lays the
 * cells out in a two-column grid following DOM order, so a phone gets
 * two clean rows - 이름/상태 and 직분/전화번호 - which is everything
 * needed to pick a person out of the list. 성별, 생년월일 and 사이트
 * 유저 are answers to questions asked about a member already found, not
 * cues for finding one, so they wait for a laptop; keeping them in DOM
 * order there leaves the desktop table exactly as it was.
 */
class MembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('이름')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold),
                TextColumn::make('status')
                    ->label('상태')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        '재적' => 'success',
                        '새가족' => 'info',
                        '장기결석' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('gender')
                    ->label('성별')
                    ->placeholder('-')
                    ->visibleFrom('lg'),
                TextColumn::make('birth_date')
                    ->label('생년월일')
                    ->placeholder('-')
                    ->date('Y-m-d')
                    ->sortable()
                    ->visibleFrom('lg'),
                TextColumn::make('position.name')
                    ->label('직분')
                    ->placeholder('-'),
                TextColumn::make('phone')
                    ->label('전화번호')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('user_id')
                    ->label('사이트 유저')
                    ->state(fn (Member $record): string => $record->user_id === null ? '없음' : '있음')
                    ->badge()
                    ->color(fn (string $state): string => $state === '있음' ? 'success' : 'gray')
                    ->sortable()
                    ->visibleFrom('lg'),
            ])
            ->filters([
                SelectFilter::make('position_id')
                    ->label('직분')
                    ->relationship('position', 'name'),
                SelectFilter::make('department')
                    ->label('부서')
                    ->options(fn (): array => Ministry::query()->orderBy('sort_order')->pluck('name', 'name')->all()),
                SelectFilter::make('gender')
                    ->label('성별')
                    ->options(['남' => '남', '여' => '여']),
                TernaryFilter::make('user_id')
                    ->label('사이트 유저')
                    ->placeholder('전체')
                    ->trueLabel('있음')
                    ->falseLabel('없음')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('user_id'),
                        false: fn (Builder $query): Builder => $query->whereNull('user_id'),
                        blank: fn (Builder $query): Builder => $query,
                    ),
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
