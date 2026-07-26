<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Sermon;
use Illuminate\Auth\Access\HandlesAuthorization;

class SermonPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Sermon');
    }

    public function view(AuthUser $authUser, Sermon $sermon): bool
    {
        return $authUser->can('View:Sermon');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Sermon');
    }

    public function update(AuthUser $authUser, Sermon $sermon): bool
    {
        return $authUser->can('Update:Sermon');
    }

    public function delete(AuthUser $authUser, Sermon $sermon): bool
    {
        return $authUser->can('Delete:Sermon');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Sermon');
    }

    public function restore(AuthUser $authUser, Sermon $sermon): bool
    {
        return $authUser->can('Restore:Sermon');
    }

    public function forceDelete(AuthUser $authUser, Sermon $sermon): bool
    {
        return $authUser->can('ForceDelete:Sermon');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Sermon');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Sermon');
    }

    public function replicate(AuthUser $authUser, Sermon $sermon): bool
    {
        return $authUser->can('Replicate:Sermon');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Sermon');
    }

}