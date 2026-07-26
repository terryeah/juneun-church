<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

/**
 * Form schema for church events (교회 행사).
 */
class EventForm
{
    /**
     * Configure the event form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('행사명')
                    ->required()
                    ->maxLength(255),
                TextInput::make('location')
                    ->label('행사장')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('event_date')
                    ->label('행사일')
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->required(),
                TimePicker::make('event_time')
                    ->label('시작 시간')
                    ->native(false)
                    ->displayFormat('h:i:s A')
                    ->seconds(true),
                DatePicker::make('end_date')
                    ->label('종료일')
                    ->native(false)
                    ->displayFormat('Y-m-d')
                    ->helperText('하루 행사는 비워두세요.'),
                Toggle::make('is_published')
                    ->label('게시')
                    ->default(true),
                Textarea::make('description')
                    ->label('설명')
                    ->columnSpanFull(),
            ]);
    }
}
