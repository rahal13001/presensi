<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ShiftAssignment;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShiftAssignmentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ShiftAssignment');
    }

    public function view(AuthUser $authUser, ShiftAssignment $shiftAssignment): bool
    {
        return $authUser->can('View:ShiftAssignment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ShiftAssignment');
    }

    public function update(AuthUser $authUser, ShiftAssignment $shiftAssignment): bool
    {
        return $authUser->can('Update:ShiftAssignment');
    }

    public function delete(AuthUser $authUser, ShiftAssignment $shiftAssignment): bool
    {
        return $authUser->can('Delete:ShiftAssignment');
    }

    public function restore(AuthUser $authUser, ShiftAssignment $shiftAssignment): bool
    {
        return $authUser->can('Restore:ShiftAssignment');
    }

    public function forceDelete(AuthUser $authUser, ShiftAssignment $shiftAssignment): bool
    {
        return $authUser->can('ForceDelete:ShiftAssignment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ShiftAssignment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ShiftAssignment');
    }

    public function replicate(AuthUser $authUser, ShiftAssignment $shiftAssignment): bool
    {
        return $authUser->can('Replicate:ShiftAssignment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ShiftAssignment');
    }

}