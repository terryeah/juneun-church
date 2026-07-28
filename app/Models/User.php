<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, LogsModelActivity, Notifiable;

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
        ];
    }
}
