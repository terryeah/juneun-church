<?php

namespace App\Filament\Resources\Ministries\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MinistryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('이름')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('description')
                    ->label('설명')
                    ->helperText('선교지 등 자유롭게 적는 칸입니다.')
                    ->maxLength(255),
            ]);
    }
}
