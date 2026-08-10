<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Database\Factories\UserFactory;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthentication;
use Filament\Auth\MultiFactor\App\Contracts\HasAppAuthenticationRecovery;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
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
class User extends Authenticatable implements FilamentUser, HasAppAuthentication, HasAppAuthenticationRecovery, HasAvatar
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
     * The admin panel avatar: the roster photo of the 성도 this account
     * belongs to, when there is one.
     *
     * Null - no linked 성도, or one with no photo on file - makes
     * Filament fall through to the panel's avatar provider, which draws
     * the account's initials.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->member?->photoUrl();
    }

    /**
     * The 가입 신청 this account came from, if it came from one at all.
     *
     * The link runs through the roster record the request was matched
     * to on approval, which is the only thing tying the two together;
     * an account an administrator created by hand has none.
     */
    public function membershipRequest(): HasOneThrough
    {
        return $this->hasOneThrough(
            MembershipRequest::class,
            Member::class,
            'user_id',
            'matched_member_id',
        );
    }

    /**
     * Record 2FA lifecycle changes and password resets by somebody
     * else in the activity log. Only the event is logged - no secret,
     * recovery code, password or hash ever leaves the model.
     */
    protected static function booted(): void
    {
        static::updated(function (User $user): void {
            /**
             * An administrator may legitimately set a new password on a
             * staff account through the 성도 form's 사이트 계정 section,
             * and the same happens when somebody completes a reset with
             * an emailed link. Neither left any trace: LogsModelActivity
             * excludes the password column, and a save that changed only
             * the password is then an empty change set that
             * dontLogEmptyChanges() drops altogether. A password being
             * set on an account by anyone other than its owner is worth
             * seeing, so it is recorded here instead.
             */
            if ($user->wasChanged('password') && auth()->id() !== $user->getKey()) {
                activity()
                    ->performedOn($user)
                    ->causedBy(auth()->user())
                    ->event('password_changed')
                    ->log('다른 사용자가 비밀번호 변경');
            }

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
     * Answer of isChurchMember(), which a page may ask more than once -
     * the 헌금 page asks twice, and each scopeVisible() asks again.
     */
    protected ?bool $isChurchMember = null;

    /**
     * Whether this account is one of the church's own 성도.
     *
     * The answer is the 교적 record the account is linked to, and only
     * that. A role cannot say it: 가입 신청 is open to anyone, and an
     * approved account is not proof that the office recognised the
     * applicant - linking them to the 교적 is. So somebody who attends
     * but was never registered signs in and reads the public site,
     * while 성도 전용 stays with the roster.
     *
     * It follows that a staff account with no 교적 record does not see
     * 성도 전용 content on the public site either. That is deliberate:
     * one rule, no exemptions, and staff accounts are only ever handed
     * to people who are already on the roster.
     */
    public function isChurchMember(): bool
    {
        return $this->isChurchMember ??= $this->member()->exists();
    }

    /**
     * Whether this account is a 일반회원 and nothing more: the
     * permissionless role every approved 가입 신청 receives.
     *
     * Such an account may sign in - it needs the panel for its own
     * profile - but reaches no dashboard, no resource and no mandatory
     * two-factor prompt. A single extra role makes it staff again.
     *
     * This says nothing about whether the person is a 성도; that is
     * isChurchMember(), and most 일반회원 are both.
     */
    public function isGeneralMember(): bool
    {
        return $this->roles->count() === 1 && $this->hasRole('general_member');
    }

    /**
     * Whether mandatory two-factor authentication is waived.
     *
     * Ordinary 성도 are waived because an authenticator app is too much
     * to ask of someone reading their own giving records. The per-role
     * test accounts are waived so each role's view of the panel can be
     * checked without registering six authenticators; they exist only
     * to be looked through, and carry generated passwords.
     */
    public function isExemptFromMultiFactorAuthentication(): bool
    {
        return $this->isGeneralMember() || $this->is_test_account;
    }

    /**
     * Whether this account runs the site: the pastor's office, the
     * developer, or a super admin. Used wherever something is meant for
     * the people who administer the church rather than help with it.
     */
    public function isAdministrator(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin', 'developer']);
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
            'is_test_account' => 'boolean',
            'is_audit_exempt' => 'boolean',
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
