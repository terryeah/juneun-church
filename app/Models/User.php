<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Application user with role-based permissions for the admin panel.
 *
 * Accounts are created by administrators only; there is no public
 * registration. Access levels are governed by Spatie roles managed
 * through Filament Shield.
 */
#[Fillable(['name', 'email', 'password', 'created_by'])]
#[Hidden(['password', 'remember_token', 'app_authentication_secret', 'app_authentication_recovery_codes'])]
class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, LogsModelActivity, Notifiable;

    /**
     * The roster record (성도) this account belongs to.
     */
    public function member(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    /**
     * Record 2FA lifecycle changes in the activity log. Only the event
     * is logged - secrets and recovery codes never leave the model.
     */
    protected static function booted(): void
    {
        static::updated(function (User $user): void {
            if ($user->wasChanged('app_authentication_secret')) {
                activity()
                    ->performedOn($user)
                    ->causedBy(auth()->user())
                    ->event($user->app_authentication_secret === null ? '2fa_disabled' : '2fa_enabled')
                    ->log($user->app_authentication_secret === null ? '2단계 인증 해제' : '2단계 인증 등록');

                return;
            }

            if ($user->wasChanged('app_authentication_recovery_codes')) {
                activity()
                    ->performedOn($user)
                    ->causedBy(auth()->user())
                    ->event('2fa_recovery_codes')
                    ->log('2단계 인증 복구 코드 재생성');
            }
        });
    }

    /**
     * Determine whether the user may access the given Filament panel.
     *
     * Any user holding at least one role may sign in to the admin panel;
     * resource-level visibility is enforced by Shield permissions.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->roles()->exists();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'app_authentication_secret' => 'encrypted',
            'app_authentication_recovery_codes' => 'encrypted:array',
        ];
    }

    /**
     * The admin who created this account; null means the system did.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by');
    }

    /**
     * The stored authenticator-app secret, if two-factor is set up.
     */
    public function getAppAuthenticationSecret(): ?string
    {
        return $this->app_authentication_secret;
    }

    /**
     * Persist (or clear) the authenticator-app secret.
     */
    public function saveAppAuthenticationSecret(?string $secret): void
    {
        $this->forceFill(['app_authentication_secret' => $secret])->save();
    }

    /**
     * The account label shown inside authenticator apps.
     */
    public function getAppAuthenticationHolderName(): string
    {
        return $this->email;
    }

    /**
     * The stored two-factor recovery codes.
     *
     * @return ?array<string>
     */
    public function getAppAuthenticationRecoveryCodes(): ?array
    {
        return $this->app_authentication_recovery_codes;
    }

    /**
     * Persist (or clear) the two-factor recovery codes.
     *
     * @param  ?array<string>  $codes
     */
    public function saveAppAuthenticationRecoveryCodes(?array $codes): void
    {
        $this->forceFill(['app_authentication_recovery_codes' => $codes])->save();
    }
}
