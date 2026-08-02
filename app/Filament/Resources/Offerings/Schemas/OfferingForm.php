<?php

namespace App\Filament\Resources\Offerings\Schemas;

use App\Models\Offering;
use Carbon\CarbonInterface;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

/**
 * Form schema for a Sunday's offering record (헌금 내역).
 */
class OfferingForm
{
    /**
     * Configure the offering form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('sunday_date')
                    ->label('주일 날짜')
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->default(today()->previousOrSame(CarbonInterface::SUNDAY))
                    ->unique(ignoreRecord: true)
                    ->required(),
                TextInput::make('note')
                    ->label('비고')
                    ->maxLength(255),
                Repeater::make('items')
                    ->label('내역')
                    ->addActionLabel('내역 추가')
                    ->columns(3)
                    ->schema([
                        Select::make('category')
                            ->label('구분')
                            ->options(Offering::CATEGORIES)
                            ->default('십일조')
                            ->required(),
                        TextInput::make('name')
                            ->label('성함'),
                        TextInput::make('amount')
                            ->label('금액')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
