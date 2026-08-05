<?php

namespace App\Filament\Resources\Offerings\RelationManagers;

use App\Filament\Resources\PersonalOfferings\Schemas\PersonalOfferingForm;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Lists the individual giving records (개인 헌금) of one Sunday; the
 * owning offering supplies the Sunday, so the form drops that field.
 */
class PersonalOfferingsRelationManager extends RelationManager
{
    protected static string $relationship = 'personalOfferings';

    protected static ?string $title = '개인 헌금';

    protected static ?string $modelLabel = '개인 헌금';

    protected static ?string $pluralModelLabel = '개인 헌금';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return PersonalOfferingForm::configure($schema, withOffering: false);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('성함')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('category')
                    ->label('구분')
                    ->placeholder('-'),
                TextColumn::make('amount')
                    ->label('금액')
                    ->placeholder('-')
                    ->formatStateUsing(fn (string $state): string => '$'.number_format((float) $state, 2)),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
