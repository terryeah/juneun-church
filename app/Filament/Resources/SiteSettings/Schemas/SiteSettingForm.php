<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SiteSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->label('키')
                    ->helperText('사이트 코드에서 이 이름으로 값을 읽어오므로 함부로 바꾸지 마세요.')
                    ->required(),
                TextInput::make('group')
                    ->label('그룹')
                    ->required()
                    ->default('general'),
                Textarea::make('value')
                    ->label('설정값')
                    ->columnSpanFull(),
            ]);
    }
}
