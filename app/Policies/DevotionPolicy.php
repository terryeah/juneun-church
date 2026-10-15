<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Devotion;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class DevotionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Devotion');
    }

    public function view(AuthUser $authUser, Devotion $devotion): bool
    {
        return $authUser->can('View:Devotion');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Devotion');
    }

    public function update(AuthUser $authUser, Devotion $devotion): bool
    {
        return $authUser->can('Update:Devotion');
    }

    public function delete(AuthUser $authUser, Devotion $devotion): bool
    {
        return $authUser->can('Delete:Devotion');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Devotion');
    }

    public function restore(AuthUser $authUser, Devotion $devotion): bool
    {
        return $authUser->can('Restore:Devotion');
    }

    public function forceDelete(AuthUser $authUser, Devotion $devotion): bool
    {
        return $authUser->can('ForceDelete:Devotion');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Devotion');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Devotion');
    }

    public function replicate(AuthUser $authUser, Devotion $devotion): bool
    {
        return $authUser->can('Replicate:Devotion');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Devotion');
    }
}
