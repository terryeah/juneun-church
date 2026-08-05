<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MembershipRequest;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MembershipRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MembershipRequest');
    }

    public function view(AuthUser $authUser, MembershipRequest $membershipRequest): bool
    {
        return $authUser->can('View:MembershipRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MembershipRequest');
    }

    public function update(AuthUser $authUser, MembershipRequest $membershipRequest): bool
    {
        return $authUser->can('Update:MembershipRequest');
    }

    /**
     * Approving hands out a login and touches the roster, so it needs
     * the same authority as editing a request.
     */
    public function approve(AuthUser $authUser, MembershipRequest $membershipRequest): bool
    {
        return $authUser->can('Update:MembershipRequest');
    }

    /**
     * Rejecting closes a request without creating an account.
     */
    public function reject(AuthUser $authUser, MembershipRequest $membershipRequest): bool
    {
        return $authUser->can('Update:MembershipRequest');
    }

    public function delete(AuthUser $authUser, MembershipRequest $membershipRequest): bool
    {
        return $authUser->can('Delete:MembershipRequest');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:MembershipRequest');
    }

    public function restore(AuthUser $authUser, MembershipRequest $membershipRequest): bool
    {
        return $authUser->can('Restore:MembershipRequest');
    }

    public function forceDelete(AuthUser $authUser, MembershipRequest $membershipRequest): bool
    {
        return $authUser->can('ForceDelete:MembershipRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MembershipRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MembershipRequest');
    }

    public function replicate(AuthUser $authUser, MembershipRequest $membershipRequest): bool
    {
        return $authUser->can('Replicate:MembershipRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MembershipRequest');
    }
}
