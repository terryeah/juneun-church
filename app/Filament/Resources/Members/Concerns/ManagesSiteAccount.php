<?php

namespace App\Filament\Resources\Members\Concerns;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Creates or updates the login account linked to a roster record from
 * the 사이트 계정 section of the member form. Turning the toggle off
 * leaves the account untouched - deleting logins is a developer task.
 */
trait ManagesSiteAccount
{
    /**
     * Apply the site-account form state to the linked user.
     */
    protected function syncSiteAccount(): void
    {
        $state = $this->form->getRawState();

        if (! ($state['site_account'] ?? false)) {
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
}
