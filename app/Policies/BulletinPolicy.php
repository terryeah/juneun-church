<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Bulletin;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class BulletinPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Bulletin');
    }

    public function view(AuthUser $authUser, Bulletin $bulletin): bool
    {
        return $authUser->can('View:Bulletin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Bulletin');
    }

    public function update(AuthUser $authUser, Bulletin $bulletin): bool
    {
        return $authUser->can('Update:Bulletin');
    }

    public function delete(AuthUser $authUser, Bulletin $bulletin): bool
    {
        return $authUser->can('Delete:Bulletin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Bulletin');
    }

    public function restore(AuthUser $authUser, Bulletin $bulletin): bool
    {
        return $authUser->can('Restore:Bulletin');
    }

    public function forceDelete(AuthUser $authUser, Bulletin $bulletin): bool
    {
        return $authUser->can('ForceDelete:Bulletin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Bulletin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Bulletin');
    }

    public function replicate(AuthUser $authUser, Bulletin $bulletin): bool
    {
        return $authUser->can('Replicate:Bulletin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Bulletin');
    }
}
