<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Resources\Members\MemberResource;
use App\Models\User;
use App\Support\RoleLabel;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Password;

/**
 * Read-only listing of site accounts. Accounts are created and edited
 * from the linked roster record (성도), not here; each row links
 * through to that roster record instead.
 *
 * A phone shows who the account belongs to, their 직분, what they can
 * do and whether they have enrolled in 2FA - the four things anybody
 * opens this list to check. How the account came to exist and when it
 * was opened are audit questions rather than scanning cues, so they
 * appear once there is a laptop's width, on top of the administrator
 * check they already carry.
 */
class UsersTable
{
    /**
     * The roles no reset link may ever be minted for.
     *
     * A reset link is a working key to the account it names, so minting
     * one for a super admin or a fellow developer hands over every
     * permission in the system - and 2FA is the only thing left in the
     * way, which an account that has not enrolled yet does not have.
     * The people holding those two roles are also the ones who can
     * always recover themselves through the panel's own reset page, so
     * refusing them costs nothing and removes the one target worth
     * taking. Everybody else - 성도 and the specialist staff roles -
     * is what the action was written for.
     *
     * @var list<string>
     */
    private const PROTECTED_ROLES = ['super_admin', 'developer'];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('이름')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold),
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
                    ->visible(fn (): bool => auth()->user()?->isAdministrator() ?? false)
                    ->visibleFrom('lg'),
                TextColumn::make('app_authentication_secret')
                    ->label('2단계 인증')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => filled($state) ? '사용 중' : '미설정')
                    ->color(fn ($state): string => filled($state) ? 'success' : 'gray')
                    ->placeholder('미설정'),
                /**
                 * The one column an access review is actually made on.
                 * The list said when each account was created but never
                 * whether it is still used, so an account belonging to a
                 * volunteer who left two years ago looked exactly like
                 * one opened every Sunday. The sign-ins are in the
                 * activity log, but only a developer may read that.
                 */
                TextColumn::make('last_login_at')
                    ->label('마지막 로그인')
                    ->dateTime('Y-m-d')
                    ->description(fn (User $record): ?string => $record->last_login_at?->diffForHumans())
                    ->placeholder('한 번도 없음')
                    ->sortable()
                    ->visible(fn (): bool => auth()->user()?->isAdministrator() ?? false),
                TextColumn::make('created_at')
                    ->label('가입일')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->visible(fn (): bool => auth()->user()?->isAdministrator() ?? false)
                    ->visibleFrom('lg'),
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
                    ->visible(fn (User $record): bool => (auth()->user()?->hasRole('developer') ?? false)
                        && static::mayMintLinkFor($record))
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
     * Whether a reset link may be minted for the given account.
     */
    public static function mayMintLinkFor(User $user): bool
    {
        return ! $user->hasAnyRole(self::PROTECTED_ROLES);
    }

    /**
     * A signed, single-use password reset link for the given account.
     *
     * The token is Laravel's own, stored hashed in password_reset_tokens
     * and expiring after the hour configured in config/auth.php, so
     * nothing recoverable about the existing password is exposed.
     *
     * Minting one is recorded in the activity log naming the developer
     * who asked and the account it opens - never the token itself,
     * which would make the log a takeover primitive of its own. The
     * restriction is re-checked here rather than trusted from the
     * action's visibility, because this is where the token is made.
     */
    public static function passwordResetUrl(User $user): string
    {
        abort_unless(static::mayMintLinkFor($user), 403);

        $url = Filament::getPanel('admin')->getResetPasswordUrl(
            Password::broker()->createToken($user),
            $user,
        );

        activity()
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->event('password_reset_link')
            ->log('비밀번호 재설정 링크 생성');

        return $url;
    }
}
