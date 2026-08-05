<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PersonalOffering;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PersonalOfferingPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PersonalOffering');
    }

    public function view(AuthUser $authUser, PersonalOffering $personalOffering): bool
    {
        return $authUser->can('View:PersonalOffering');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PersonalOffering');
    }

    public function update(AuthUser $authUser, PersonalOffering $personalOffering): bool
    {
        return $authUser->can('Update:PersonalOffering');
    }

    public function delete(AuthUser $authUser, PersonalOffering $personalOffering): bool
    {
        return $authUser->can('Delete:PersonalOffering');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PersonalOffering');
    }

    public function restore(AuthUser $authUser, PersonalOffering $personalOffering): bool
    {
        return $authUser->can('Restore:PersonalOffering');
    }

    public function forceDelete(AuthUser $authUser, PersonalOffering $personalOffering): bool
    {
        return $authUser->can('ForceDelete:PersonalOffering');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PersonalOffering');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PersonalOffering');
    }

    public function replicate(AuthUser $authUser, PersonalOffering $personalOffering): bool
    {
        return $authUser->can('Replicate:PersonalOffering');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PersonalOffering');
    }
}
