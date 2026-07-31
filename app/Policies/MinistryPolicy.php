<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Ministry;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MinistryPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Ministry');
    }

    public function view(AuthUser $authUser, Ministry $ministry): bool
    {
        return $authUser->can('View:Ministry');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Ministry');
    }

    public function update(AuthUser $authUser, Ministry $ministry): bool
    {
        return $authUser->can('Update:Ministry');
    }

    public function delete(AuthUser $authUser, Ministry $ministry): bool
    {
        return $authUser->can('Delete:Ministry');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Ministry');
    }

    public function restore(AuthUser $authUser, Ministry $ministry): bool
    {
        return $authUser->can('Restore:Ministry');
    }

    public function forceDelete(AuthUser $authUser, Ministry $ministry): bool
    {
        return $authUser->can('ForceDelete:Ministry');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Ministry');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Ministry');
    }

    public function replicate(AuthUser $authUser, Ministry $ministry): bool
    {
        return $authUser->can('Replicate:Ministry');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Ministry');
    }
}
