<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Spatie\Activitylog\Models\Activity;

/**
 * Restricts the activity log to users holding the developer role.
 *
 * The log is read-only for everyone; records are only removed by the
 * scheduled retention clean-up.
 */
class ActivityPolicy
{
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->hasRole('developer');
    }

    public function view(AuthUser $authUser, Activity $activity): bool
    {
        return $authUser->hasRole('developer');
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser, Activity $activity): bool
    {
        return false;
    }

    public function delete(AuthUser $authUser, Activity $activity): bool
    {
        return false;
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return false;
    }
}
