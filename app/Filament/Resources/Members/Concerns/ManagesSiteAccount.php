<?php

namespace App\Filament\Resources\Members\Concerns;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Creates, updates and revokes the login account linked to a roster
 * record from the 사이트 계정 section of the member form.
 *
 * Turning the toggle off deletes the account outright rather than
 * merely unlinking it. Unlinking would revoke nothing: the public
 * /login route authenticates on credentials alone without consulting
 * canAccessPanel(), the panel's own password-reset page hands a new
 * password back to anyone who knows the address, and the orphaned row
 * would keep its unique email for ever - which matters because
 * MembershipRequestController silently drops any 가입 신청 whose email
 * already belongs to an account, and 사이트 유저 is a read-only listing
 * with no way to remove the leftover. Deleting closes every door at
 * once and leaves nothing an administrator cannot reach.
 *
 * Every foreign key pointing at users is ON DELETE SET NULL, so the
 * delete never fails; what it costs is attribution. The 작성자 stamp on
 * content the account created is cleared and its past activity-log rows
 * lose their causer, which is why the confirmation modal says so in
 * plain words. The deletion itself is recorded: User logs its own
 * deleted event through LogsModelActivity, causer and all.
 */
trait ManagesSiteAccount
{
    /**
     * One-shot consent recorded by the confirmation modal.
     */
    public bool $hasConfirmedSiteAccountRevocation = false;

    /**
     * The modal shown when a login is about to be deleted.
     */
    public function confirmSiteAccountRevocationAction(): Action
    {
        return Action::make('confirmSiteAccountRevocation')
            ->requiresConfirmation()
            ->color('danger')
            ->modalHeading('로그인 계정을 삭제할까요?')
            ->modalDescription(fn (): string => sprintf(
                '%s 님의 로그인 계정(%s)이 삭제됩니다. 더 이상 로그인할 수 없고, 등록된 2단계 인증과 이 계정이 올린 글의 작성자 표시도 함께 사라집니다. 되돌리려면 계정을 새로 만들어야 합니다.',
                $this->record?->name ?? '이 성도',
                $this->record?->user?->email ?? '',
            ))
            ->modalSubmitActionLabel('계정 삭제')
            ->modalCancelActionLabel('취소')
            ->action(function (): void {
                $this->hasConfirmedSiteAccountRevocation = true;

                $this->{$this->getSubmitFormLivewireMethodName()}();
            });
    }

    /**
     * Stop a save that would delete a login until it is confirmed.
     *
     * CreateRecord never fires this hook, and a record being created
     * has no login to revoke, so the guard belongs to the edit page
     * alone even though both pages share the trait.
     */
    protected function beforeSave(): void
    {
        if (! $this->revokesSiteAccount()) {
            $this->hasConfirmedSiteAccountRevocation = false;

            return;
        }

        /**
         * Nobody may delete the account they are signed in with: the
         * save is refused outright rather than confirmed, because
         * confirming it would lock the administrator out of the panel
         * that is the only place the account can be made again.
         */
        if ($this->record->user_id === auth()->id()) {
            Notification::make()
                ->danger()
                ->title('본인 계정은 삭제할 수 없습니다')
                ->body('지금 로그인 중인 계정입니다. 삭제하면 곧바로 로그아웃되고 다시 들어올 수 없습니다. 다른 관리자에게 요청해 주세요.')
                ->persistent()
                ->send();

            throw new Halt;
        }

        if ($this->hasConfirmedSiteAccountRevocation) {
            $this->hasConfirmedSiteAccountRevocation = false;

            return;
        }

        $this->mountAction('confirmSiteAccountRevocation');

        throw new Halt;
    }

    /**
     * Whether this save switches the 사이트 계정 toggle off on a roster
     * record that actually holds a login.
     */
    protected function revokesSiteAccount(): bool
    {
        return ! ($this->form->getRawState()['site_account'] ?? false)
            && $this->record?->user_id !== null;
    }

    /**
     * Apply the site-account form state to the linked user.
     */
    protected function syncSiteAccount(): void
    {
        $state = $this->form->getRawState();

        if (! ($state['site_account'] ?? false)) {
            $this->revokeSiteAccount();

            return;
        }

        $member = $this->record;
        $user = $member->user ?? new User;

        $user->name = $member->name;
        $user->email = $state['site_email'];

        if (filled($state['site_password'] ?? null)) {
            $user->password = Hash::make($state['site_password']);
        }

        $user->save();
        $user->syncRoles(
            Role::query()->whereIn('id', (array) ($state['site_roles'] ?? []))->get(),
        );

        if ($member->user_id !== $user->id) {
            $member->forceFill(['user_id' => $user->id])->saveQuietly();
        }
    }

    /**
     * Delete the login linked to this roster record.
     *
     * The link is cleared before the account goes so the record and the
     * form the page re-renders both agree the member has no login; the
     * two writes share a transaction so a failed delete can never leave
     * a detached account squatting on its email address. Spatie clears
     * the role and permission rows on delete, and the session row stops
     * resolving to a user, so anyone signed in on it is signed out.
     */
    protected function revokeSiteAccount(): void
    {
        $user = $this->record->user;

        if (! $user) {
            return;
        }

        DB::transaction(function () use ($user): void {
            $this->record->forceFill(['user_id' => null])->saveQuietly();

            $user->delete();
        });

        $this->record->unsetRelation('user');
    }
}
