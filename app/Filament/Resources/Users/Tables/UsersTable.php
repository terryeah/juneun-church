<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Members\MemberResource;
use App\Models\User;
use App\Support\RoleLabel;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Password;

/**
 * Read-only listing of site accounts. Accounts are created and edited
 * from the linked roster record (성도), not here; each row links
 * through to that roster record instead.
 */
class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('이름')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('member.position.name')
                    ->label('직분')
                    ->placeholder('-'),
                TextColumn::make('roles.name')
                    ->label('롤')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => RoleLabel::label($state)),
                /**
                 * How the account came to exist. A 가입 신청 leaves a
                 * request row behind, so its absence is what marks an
                 * account the office registered by hand.
                 */
                TextColumn::make('membershipRequest.status')
                    ->label('가입 경로')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (): string => '가입 신청')
                    ->placeholder('관리자 등록')
                    ->visible(fn (): bool => auth()->user()?->isAdministrator() ?? false),
                TextColumn::make('app_authentication_secret')
                    ->label('2단계 인증')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => filled($state) ? '사용 중' : '미설정')
                    ->color(fn ($state): string => filled($state) ? 'success' : 'gray')
                    ->placeholder('미설정'),
                TextColumn::make('created_at')
                    ->label('가입일')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->visible(fn (): bool => auth()->user()?->isAdministrator() ?? false),
            ])
            ->recordUrl(fn (User $record): ?string => $record->member
                ? MemberResource::getUrl('edit', ['record' => $record->member])
                : null)
            ->recordActions([
                /**
                 * Helping a locked-out 성도 back in without anyone ever
                 * handling their password. Restricted to the developer,
                 * like the activity log, because the link is a
                 * single-use key to somebody else's account.
                 */
                Action::make('passwordResetLink')
                    ->label('비밀번호 재설정 링크')
                    ->icon(Heroicon::OutlinedKey)
                    ->color('gray')
                    ->visible(fn (): bool => auth()->user()?->hasRole('developer') ?? false)
                    ->modalHeading('비밀번호 재설정 링크')
                    ->modalDescription('한 시간 동안만 유효하며, 본인에게 직접 전달하세요. 새 링크를 만들면 이전 링크는 무효가 됩니다.')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('닫기')
                    ->schema([
                        TextEntry::make('passwordResetLink')
                            ->hiddenLabel()
                            ->state(fn (User $record): string => static::passwordResetUrl($record))
                            ->copyable()
                            ->copyMessage('링크를 복사했습니다'),
                    ]),
            ])
            ->toolbarActions([]);
    }

    /**
     * A signed, single-use password reset link for the given account.
     *
     * The token is Laravel's own, stored hashed in password_reset_tokens
     * and expiring after the hour configured in config/auth.php, so
     * nothing recoverable about the existing password is exposed.
     */
    public static function passwordResetUrl(User $user): string
    {
        return Filament::getPanel('admin')->getResetPasswordUrl(
            Password::broker()->createToken($user),
            $user,
        );
    }
}
