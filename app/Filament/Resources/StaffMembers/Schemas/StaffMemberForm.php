<?php

namespace App\Filament\Resources\StaffMembers\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

/**
 * Form schema for serving members (섬기는 사람들).
 */
class StaffMemberForm
{
    /**
     * Configure the staff member form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('이름')
                    ->required()
                    ->maxLength(255),
                Select::make('position_id')
                    ->label('직분')
                    ->relationship('position', 'name')
                    ->required(),
                TextInput::make('department')
                    ->label('부서 / 사역')
                    ->maxLength(255),
                FileUpload::make('photo')
                    ->label('사진')
                    ->image()
                    ->imageEditor()
                    ->disk(config('filesystems.media'))
                    ->directory('staff')
                    ->visibility('public'),
                Textarea::make('bio')
                    ->label('소개')
                    ->columnSpanFull(),
                TextInput::make('email')
                    ->label('이메일')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->label('전화번호')
                    ->tel()
                    ->maxLength(255),
                TextInput::make('sort_order')
                    ->label('정렬 순서')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_published')
                    ->label('게시')
                    ->default(true),
            ]);
    }
}
