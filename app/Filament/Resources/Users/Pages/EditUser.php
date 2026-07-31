<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Auth\MultiFactor\App\Actions\SetUpAppAuthenticationAction;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            $this->setUpTwoFactorAction(),
            $this->disableTwoFactorAction(),
            DeleteAction::make(),
        ];
    }

    /**
     * Set up an authenticator app for your own account, straight from
     * the edit page. Filament's setup flow always targets the signed-in
     * user, so it only appears when you are editing your own account and
     * two-factor is not yet enabled.
     */
    protected function setUpTwoFactorAction(): Action
    {
        return SetUpAppAuthenticationAction::make(AppAuthentication::make()->recoverable())
            ->label('2단계 인증 설정')
            ->button()
            ->visible(fn (): bool => $this->record->getKey() === auth()->id()
                && blank($this->record->getAppAuthenticationSecret()));
    }

    /**
     * Remove two-factor from the account being edited - either your own,
     * or another user who has been locked out and needs a reset.
     */
    protected function disableTwoFactorAction(): Action
    {
        return Action::make('disableTwoFactor')
            ->label('2단계 인증 해제')
            ->color('danger')
            ->icon('heroicon-o-lock-open')
            ->requiresConfirmation()
            ->modalDescription('이 계정의 2단계 인증을 해제합니다. 다음 로그인부터는 비밀번호만 필요합니다.')
            ->visible(fn (): bool => filled($this->record->getAppAuthenticationSecret()))
            ->action(function (): void {
                /** @var User $user */
                $user = $this->record;
                $user->saveAppAuthenticationSecret(null);
                $user->saveAppAuthenticationRecoveryCodes(null);

                Notification::make()
                    ->title('2단계 인증이 해제되었습니다.')
                    ->success()
                    ->send();
            });
    }
}
