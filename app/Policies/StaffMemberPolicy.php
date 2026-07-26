<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\StaffMember;
use Illuminate\Auth\Access\HandlesAuthorization;

class StaffMemberPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StaffMember');
    }

    public function view(AuthUser $authUser, StaffMember $staffMember): bool
    {
        return $authUser->can('View:StaffMember');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StaffMember');
    }

    public function update(AuthUser $authUser, StaffMember $staffMember): bool
    {
        return $authUser->can('Update:StaffMember');
    }

    public function delete(AuthUser $authUser, StaffMember $staffMember): bool
    {
        return $authUser->can('Delete:StaffMember');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:StaffMember');
    }

    public function restore(AuthUser $authUser, StaffMember $staffMember): bool
    {
        return $authUser->can('Restore:StaffMember');
    }

    public function forceDelete(AuthUser $authUser, StaffMember $staffMember): bool
    {
        return $authUser->can('ForceDelete:StaffMember');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StaffMember');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StaffMember');
    }

    public function replicate(AuthUser $authUser, StaffMember $staffMember): bool
    {
        return $authUser->can('Replicate:StaffMember');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StaffMember');
    }

}