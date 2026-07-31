<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

/**
 * Form schema for admin panel user accounts.
 *
 * Accounts are invite-only; an administrator sets the initial password
 * and assigns roles here. Ordinary admins may not grant super_admin.
 */
class UserForm
{
    /**
     * Configure the user form.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('이름')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('이메일')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('비밀번호')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->helperText('수정 시 비워두면 기존 비밀번호가 유지됩니다.'),
                Select::make('roles')
                    ->label('롤')
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: function ($query) {
                            $user = auth()->user();

                            if (! $user?->hasRole('super_admin')) {
                                $query->where('name', '!=', 'super_admin');
                            }

                            /** The developer role is invisible to non-developers. */
                            if (! $user?->hasRole('developer')) {
                                $query->where('name', '!=', 'developer');
                            }

                            return $query;
                        },
                    )
                    ->multiple()
                    ->preload(),
            ]);
    }
}
