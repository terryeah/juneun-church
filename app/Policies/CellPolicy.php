<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Cell;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CellPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Cell');
    }

    public function view(AuthUser $authUser, Cell $cell): bool
    {
        return $authUser->can('View:Cell');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Cell');
    }

    public function update(AuthUser $authUser, Cell $cell): bool
    {
        return $authUser->can('Update:Cell');
    }

    public function delete(AuthUser $authUser, Cell $cell): bool
    {
        return $authUser->can('Delete:Cell');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Cell');
    }

    public function restore(AuthUser $authUser, Cell $cell): bool
    {
        return $authUser->can('Restore:Cell');
    }

    public function forceDelete(AuthUser $authUser, Cell $cell): bool
    {
        return $authUser->can('ForceDelete:Cell');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Cell');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Cell');
    }

    public function replicate(AuthUser $authUser, Cell $cell): bool
    {
        return $authUser->can('Replicate:Cell');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Cell');
    }
}
