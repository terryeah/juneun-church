<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Photo;
use Illuminate\Auth\Access\HandlesAuthorization;

class PhotoPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Photo');
    }

    public function view(AuthUser $authUser, Photo $photo): bool
    {
        return $authUser->can('View:Photo');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Photo');
    }

    /**
     * Contributors may edit only photos they uploaded themselves.
     */
    public function update(AuthUser $authUser, Photo $photo): bool
    {
        return $authUser->can('Update:Photo')
            || ($authUser->can('Create:Photo') && $photo->uploaded_by === $authUser->getKey());
    }

    /**
     * Contributors may delete only photos they uploaded themselves.
     */
    public function delete(AuthUser $authUser, Photo $photo): bool
    {
        return $authUser->can('Delete:Photo')
            || ($authUser->can('Create:Photo') && $photo->uploaded_by === $authUser->getKey());
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Photo');
    }

    public function restore(AuthUser $authUser, Photo $photo): bool
    {
        return $authUser->can('Restore:Photo');
    }

    public function forceDelete(AuthUser $authUser, Photo $photo): bool
    {
        return $authUser->can('ForceDelete:Photo');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Photo');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Photo');
    }

    public function replicate(AuthUser $authUser, Photo $photo): bool
    {
        return $authUser->can('Replicate:Photo');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Photo');
    }

}