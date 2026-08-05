<?php

namespace App\Filament\Resources\PersonalOfferings\Schemas;

use App\Models\Member;
use App\Models\Offering;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

/**
 * Form schema for an individual giving record (개인 헌금).
 */
class PersonalOfferingForm
{
    /**
     * Configure the personal offering form.
     *
     * The Sunday select is dropped when the form is used inside the
     * offering relation manager, where the owning record already fixes
     * which Sunday the giving belongs to.
     */
    public static function configure(Schema $schema, bool $withOffering = true): Schema
    {
        $offering = $withOffering ? [
            Select::make('offering_id')
                ->label('주일')
                ->options(fn (): array => Offering::query()
                    ->orderByDesc('sunday_date')
                    ->pluck('sunday_date', 'id')
                    ->map(fn ($date): string => $date->toDateString())
                    ->all())
                ->searchable()
                ->required(),
        ] : [];

        return $schema
            ->components([
                ...$offering,
                Select::make('member_id')
                    ->label('성도')
                    ->options(fn (): array => Member::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->live()
                    ->helperText('명단에 없는 분은 비워두고 성함만 적어 주세요.')
                    ->afterStateUpdated(fn (Set $set, ?string $state) => filled($state)
                        ? $set('name', Member::query()->whereKey($state)->value('name'))
                        : null),
                TextInput::make('name')
                    ->label('성함')
                    ->required()
                    ->maxLength(255),
                Select::make('category')
                    ->label('구분')
                    ->options(Offering::CATEGORIES)
                    ->default('십일조')
                    ->required(),
                TextInput::make('amount')
                    ->label('금액')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                TextInput::make('note')
                    ->label('비고')
                    ->maxLength(255),
            ]);
    }
}
